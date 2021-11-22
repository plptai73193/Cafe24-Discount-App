<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Services\ProductService;
use App\Models\Logs\DiscountApiLog;
use App\Facades\Cafe24\Cafe24;

class ProductController extends BaseController{

    /**
     * Get Discount Price
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
    public function getDiscountPrice(request $request){
        $params  = [
            "mall_id" => $request->mall_id,
            "product" => $request->product,
            "shop_no" => $request->shop_no,
            "member_id" => $request->member_id,
            "group_no" => $request->group_no,
            "time" => $request->time,
        ];
        $product_service = new ProductService();
        $api_result = $product_service->calculateDiscountPrice($params);
        return $api_result;
    }
}
