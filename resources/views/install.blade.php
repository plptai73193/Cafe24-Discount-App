<?php
use App\Libs\Cafe24\Cafe24Token;
use App\Models\Mall;



$client_id = (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_ID') : env('CAFE24_APP_CLIENT_ID_DEV');
$client_secret = (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_SECRET') : env('CAFE24_APP_CLIENT_SECRET_DEV');
$redirect_uri = (env('APP_ENV') == "production") ? env('CAFE24_APP_REDIRECT_URI') : env('CAFE24_APP_REDIRECT_URI_DEV');
$app_code = env("CAFE24_APP_CODE");

/******** App Setting ********/
$app_setting = [
    "mall_id"               =>      @$_REQUEST["mall_id"] ? $_REQUEST["mall_id"] : "",
    "state"                 =>      env("CAFE24_APP_STATE"),
    "client_id"             =>      $client_id,
    "client_secret"         =>      $client_secret,
    "redirect_uri"          =>      $redirect_uri,
    "scope"                 =>      env("CAFE24_APP_SCOPE"),
    "code"                  =>      @$_GET["code"] ? @$_GET["code"] : $app_code,
];
/******** App Setting ********/

$error = Cafe24Token::getError($_GET);
if (!empty($error)) {
    echo $error;
    return false;
}



/* First Setup App */
Cafe24Token::installApp($app_setting);


/* Get Installed Mall Id App */
$http_request = $_SERVER["HTTP_REFERER"];
$mall_id_installed = Cafe24Token::getInstalledMallId($http_request);
$app_setting["mall_id"] = $mall_id_installed;


/* Get token */
$tokenData = Cafe24Token::getToken($app_setting);
$code = @$_GET["code"] ? @$_GET["code"] : $app_code;

if (!empty($tokenData->error)) {
    $error = $tokenData->error;
    $error_description = $tokenData->error_description;
    $error_msg = "[{$error}] {$error_description}";
    echo $error_msg;
} else {
    $access_token = $tokenData->access_token;
    $refresh_token = $tokenData->refresh_token;
    $client_id = $tokenData->client_id;
    $cafe_mall_id = $tokenData->mall_id;
    $scopes = $tokenData->scopes;

    /* Validate mall info and insert Source Mall */
    if (!empty($cafe_mall_id) && !empty($access_token) && !empty($refresh_token)) {
        $shop = null;
        $all_shops = null;

        $app_url = (env('APP_ENV') == "production") ? env('APP_URL') : env('APP_URL_DEV');
        
        $mall_param = [
            "cafe_mall_id" => $cafe_mall_id,
            "access_token" => $access_token,
            "refresh_token" => $refresh_token,
        ];
        
        // var_dump($mall_param);die();
        /* Create new Mall if not existed */
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$app_url}/api/v1/mall/store",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $mall_param,
        ));
        $response_curl = curl_exec($curl);
        $err_curl = curl_error($curl);
        curl_close($curl);
        if ($err_curl) {
            echo 'cURL Error #:' . $err_curl;
        } else {
            $all_shops = $response_curl;
            $response_curl = json_decode($response_curl, true);
            // dd($all_shops);
            if ($response_curl["success"] == false) {
                echo $response_curl["msg"];
            } else {
                $msg = $response_curl["msg"];
                $datas = $response_curl["data"];
                $shops = $datas["mall"];
                $logs = $datas["logs"];
                

                $select = [
                    "id",
                    "cafe_mall_id",
                    "shop_no",
                    "access_token",
                    "refresh_token",
                ];

                $where = [
                    "cafe_mall_id" => $shops['cafe_mall_id'],
            ];

                $malls = Mall::select($select)->where($where)->get()->toArray();
                if (!empty($malls)) {
                    foreach ($malls as $mall) {
                        $shop_no = $mall['shop_no'];
                        $scripttag_params = [
                            "cafe_mall_id" => $cafe_mall_id,
                            "shop_no" => $shop_no,
                        ];
                        // install CAFE24API
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "{$app_url}/api/v1/mall/cafe24api",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_POSTFIELDS => $scripttag_params,
                        ));
                        $response_curl = curl_exec($curl);
                        $err_curl = curl_error($curl);
                        curl_close($curl);
                    }
                    echo "<h1>Discount App Installed</h1>";
                }
            }
            // if ($all_shops["success"] === true && !empty($all_shops["data"])) {
                
            // }
        }
        /* Create new Mall if not existed:E */
        ?>

    <?php }
}
?>