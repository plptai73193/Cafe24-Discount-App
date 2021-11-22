<?php
namespace App\Services;

use App\Services\BaseService;
use App\Models\Mall;
use App\Facades\Cafe24\Cafe24Api;
use App\Facades\Cafe24\Cafe24;

class MallService extends BaseService {

    public function __construct() {
		$this->model = new Mall();
	}

    /**
     * Create mall
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
    public function createMall(array $params, $access_token){
        $result = [
            "success" => false,
            "data" => [],
            "errors" => [],
            "msg" => "",
        ];

        $cafe_mall_id = $params["cafe_mall_id"];
        $refresh_token = $params["refresh_token"];

        $endpoint_shops = "shops";
        $cf_params = [];
        $res_data = Cafe24Api::get($cafe_mall_id, $access_token, $endpoint_shops, $cf_params);
        
        if ($res_data["success"] == false) {
            $result = $res_data;
            return $result;
        } else {
            $data = $res_data["data"];
            $shops = $data->shops;
            if (count($shops) > 0) {

                $default_mall_id = "";
                $default_shop_no = "";
                $default_mall_name = "";
                $default_access_token = "";
                foreach ($shops as $_shop) {
                    $shop_name = $_shop->shop_name;
                    $shop_no = $_shop->shop_no;
                    $language = $_shop->language_code;
                    $mall_domain = !empty($_shop->primary_domain) ? $_shop->primary_domain : $_shop->base_domain;
                    $insert_shop = [
                        "cafe_mall_id" => $cafe_mall_id,
                        "shop_no" => $shop_no,
                        "mall_name" => $shop_name,
                        "mall_url" => $mall_domain,
                        "language" => $language,
                        "access_token" => $access_token,
                        "refresh_token" => $refresh_token,
                        "created_at" => time(),
                        "updated_at" => time(),
                    ];

                    //save Mall to database
                    $mall = Mall::create($insert_shop);
                    
                    //get default shop
                    if ($_shop->default == "T") {
                        $default_mall_id = $mall->id;
                        $default_shop_no = $shop_no;
                        $default_mall_name = $shop_name;
                        $default_access_token = $access_token;
                        $result["data"] = $mall;
                    }
                }

                // tạo setting mặc định cho mall
                /* $settingService = new SettingService();
                $settingService->createDefaultOptions($default_mall_id, $cafe_mall_id); */


                // tạo mall_email mặc định cho mall
                /* $endpoint_store = "store?shop_no={$default_shop_no}";
                $store_data = Cafe24Api::get($cafe_mall_id, $default_access_token, $endpoint_store);
                if ($store_data["success"]) {
                    $store = $store_data["data"]->store;
                    $store_email = $store->email;
                    $mall_email_info = [
                        "email" => $store_email,
                        "mall_name" => $default_mall_name,
                    ];
                    $mallEmailService = new MallEmailService();
                    $mallEmailService->createDefaultMallEmail($cafe_mall_id, $mall_email_info);
                } */
               
                $result["success"] = true;
            } else {
                $result["success"] = false;
                $result["msg"] = "Cafe24 Error!";
                return $result;
            }
        }
        return $result;
    }

    
    /**
     * Get mall and create if not exist
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
    public function getMallAndCreate($params, $access_token){
        try {
            $result = [
                "success" => false,
                "data" => [
                    "is_first" => false,
                ],
                "msg" => "",
            ];
            
            $mall_where = [
                "cafe_mall_id" => $params["cafe_mall_id"],
            ];
            
            $mall = $this->getModel($mall_where);

            /*********** create mall if not existed ***********/
            if (empty($mall)) {
                if (empty($access_token)) {
                    $result["success"] = false;
                    $result["msg"] = "access_token is null";
                    return $result;
                } else {
                    $insert_mall = $this->createMall($params, $access_token);
                    if ($insert_mall["success"] === true) {
                        $mall = $insert_mall["data"];
                        $result["data"]["is_first"] = true;
                    } else {
                        $result = $insert_mall;
                        return $result;
                    }
                }
            }

            $result["success"] = true;
            $result["data"]["mall"] = $mall;
            return $result;

        } catch (Exception $e) {
            $this->getError($e);
        }
            
    }


    /**
     * Create Query_Builder to get models
     *
     * @param array $where
     * @param array $columns
     * @param string $whereRaw
     * @param array $prepare_whereRaw
     * @param array $group_by_columns
     *
     * @return object $builder
     */
    public function getModelQueryBuilder($where = [], $columns = [], $whereRaw = "", $prepare_whereRaw = [], $group_by_columns = []){
        try {
            /*********** parammeter ***********/
            extract($where);
            
            $builder = $this->model::query();

            /* get columns  */
            if (!empty($columns)) {
                $builder->select($columns);
            }

            /* group by columns  */
            if (!empty($group_by_columns)) {
                $builder->groupBy($group_by_columns);
            }

            /*********** search conditions  ***********/
            $conditions = [
                "is_app_disabled" => "0",           //default
                "is_app_deleted" => "0",            //default
                "is_app_expired" => "0",            //default
            ];
            
            foreach ($where as $key => $value) {
                $conditions[$key] = $value;
            }
            $builder = $builder->where($conditions);


            /* search by dynamic conditions */
            if (!empty($whereRaw)) {
                $builder = $builder->whereRaw($whereRaw, $prepare_whereRaw);
            }

            return $builder;

        } catch (Exception $e) {
            $this->getError($e);
        }
    }

    /**
     * Check if mall exists
     *
     * @param string $mall_id
     *
     * @return boolean $result
     */
    public function isMallExisted($mall_id){
        try {
            $result = false;
            $whereRaw = "id = ? OR cafe_mall_id = ?";
            $prepare_whereRaw = [$mall_id, $mall_id];
            $mall = $this->getModel([], [], $whereRaw, $prepare_whereRaw);
            if (!empty($mall)) {
                $result = true;
            }
            return $result;

        } catch (Exception $e) {
            $this->getError($e);
        }
    }


    /**
     * Create Scripttag
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
    public function installCAFE24API(array $params){
        $result = [
            "success" => false,
            "data" => [],
            "errors" => [],
            "msg" => "",
        ];
        $cafe_mall_id = $params["cafe_mall_id"];
        $shop_no = $params["shop_no"];
        $theme_params = [
            "theme_type" => [
                "pc",
                "mobile"
            ],
            "cafe_mall_id" => $cafe_mall_id,
        ];

        // Call List all themes API to get the skin_no
        $api_result = $this->getAllThemes($theme_params);
        if ($api_result["success"] == true && !empty($api_result["data"])) {
            $themes_data = $api_result["data"];
            // $skin_no[];
            foreach ($themes_data as $theme) {
                $skin_no[] = $theme->skin_no; //parse skin_no
            }
            

            // Install Scripttag
            $mall_tokens = Cafe24::getCafe24Token();
            $access_token = $mall_tokens['access_token'];
            $endpoint_shops = "scripttags";
            $cf_params = [
                "shop_no" => $shop_no,
                "request" => [
                    "src"=> "https://app4you.cafe24.com/SmartPopup/tunnel/scriptTags?vs=20210128144637.1",
                    "display_location" => [
                        "ORDER_BASKET",
                        "ORDER_ORDERFORM",
                        "ORDER_ORDERRESULT",
                        "ORDER_GIFTLIST"
                    ],
                    "skin_no" => $skin_no,
                    "integrity" => "sha384-j+WLOriOo0/sb+Ho5fn6lGPknv0cW+wMxOLlx8qpy01ShkkynynGNJQ53niqAdze"
                ],
            ];
            $res_data = Cafe24Api::post($cafe_mall_id, $access_token, $endpoint_shops, $cf_params);
            if ($res_data["success"] == true) {
                $result['success'] = true;
                $result['data'] = $res_data['data'];
            }
            $result['msg'] = $res_data['msg'];
        }
        return $result;
    }



    /**
     * Create Scripttag
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
    public function getAllThemes($params){
        $result = [
            "success" => false,
            "data" => [],
            "errors" => [],
            "msg" => "",
        ];
        $theme_types = $params["theme_type"];
        $cafe_mall_id = $params["cafe_mall_id"];
        $mall_tokens = Cafe24::getCafe24Token();
        $access_token = $mall_tokens['access_token'];
        foreach ($theme_types as $type) {
            $endpoint = "themes?type={$type}";
            $cf_params = [];
            $res_data = Cafe24Api::get($cafe_mall_id, $access_token, $endpoint, $cf_params);
            if ($res_data['success'] == true && !empty($res_data['data']->themes)) {
                $themes = $res_data['data']->themes;
                foreach ($themes as $theme) {
                    $temp_data[] = $theme;
                }
                $result["success"] = true;
                $result["data"] = $temp_data;
            } else {
                $result["msg"] = "No data found!";
            }
        }
        return $result;
    }
}