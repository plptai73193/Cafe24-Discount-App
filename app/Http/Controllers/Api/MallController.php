<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Services\MallService;
use App\Models\Logs\MallAccessLog;
use App\Facades\Cafe24\Cafe24;

class MallController extends BaseController {
    protected $mallService;

    public function __construct(MallService $mallService){
        $this->mallService = $mallService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cafe_mall_id = $request->cafe_mall_id;
        $access_token = $request->access_token;
        $refresh_token = $request->refresh_token;

        $result = [
            "success" => false,
            "data" => [],
            "msg" => "",
        ];
        $getMallParams = [
            "cafe_mall_id" => $cafe_mall_id,
        ];
        
        $mall = $this->mallService->getModel($getMallParams);
        
        if (!empty($mall)) {
            /* update mall access_token and refresh_token */
            Cafe24::updateMallToken($cafe_mall_id, $access_token, $refresh_token);

            
            /* prepare mall info for frontend */
            $result["success"] = true;
            $result["data"]["mall"] = $mall;
            $result["data"]["logs"] = MallAccessLog::where(["mall_id" => $mall["id"]])->paginate(10);
        } else {
            $result["success"] = false;
            $result["msg"] = "This mall does not exist";
        }
        return $result;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function CAFE24API(Request $request){
        $params = [
            "cafe_mall_id" => $request->cafe_mall_id,
            "shop_no" => $request->shop_no,
        ];
        $mall_servie = new MallService();
        $api_call = $mall_servie->installCAFE24API($params);
        return $api_call;
    }
}
