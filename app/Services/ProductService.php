<?php
namespace App\Services;

use App\Services\BaseService;
use App\Facades\Cafe24\Cafe24Api;
use App\Facades\Cafe24\Cafe24;
use App\Models\Logs\DiscountApiLog;
use App\Models\Mall;

class ProductService {


   public function __construct() {
      $this->client_secret = (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_SECRET') : env('CAFE24_APP_CLIENT_SECRET_DEV');;
	}


   /**
     * Get Discount Price
     *
     * @param array $params
     * @param string $access_token
     *
     * @return array $result
     */
   public function calculateDiscountPrice(array $params){
      $result = [
         "code" => 400,
         "message" => "",
         "data" => [],
         
      ];

      $require_field = [
         "mall_id",
         "shop_no",
         "member_id",
         "time",
      ];
      
      foreach ($require_field as $field) {
         foreach ($params as $key => $value) {
            if ($key == $field && empty($value)) {
               $result['msg'] = "{$key} is mandatory";
               return $result;
            }
         }
      }
      
      
      $ec_mall_id = $params["mall_id"];
      $cart_products = $params["product"];
      $shop_no = $params["shop_no"];
      $member_id = $params["member_id"];
      $group_no = $params["group_no"];
      $time = $params["time"];
      $discount_percent = 30;

      $select = [
         "id",
         "cafe_mall_id",
         "access_token",
         "refresh_token",
      ];

      $where = [
         "cafe_mall_id" => $ec_mall_id
      ];

      $mall = Mall::select($select)->where($where)->get();
      $access_token = $mall[0]->access_token;
      $mall_id = $mall[0]->id;



      if (!empty($cart_products)) {
         foreach ($cart_products as $cart_product) {
            $cart_product_nos[] = $cart_product['product_no'];
         }
         // Get Product ID from healthy-juice category
         $endpoint_products = "products";
         $cf_params = [
            "shop_no" => $shop_no,
            "category" => 64,
            "limit" => 100,
         ];
         $res_data = Cafe24Api::get($ec_mall_id, $access_token, $endpoint_products, $cf_params);
         if ($res_data['success'] == true && !empty($res_data['data']->products)) {
            $products = $res_data['data']->products;
            $category_product_array = [];
            foreach ($products as $product) {
               $category_product_array[] = $product->product_no;
            }


            //Compare all product_no in Cart with all product_no in healthy-juice category
            $i = 0;
            foreach ($cart_product_nos as $cart_product_no) {
               $cart_product_no = (int) $cart_product_no;
               if (in_array($cart_product_no, $category_product_array)) {
                  $i++;
               }
            }


            // if there is more then 3 healthy-juice products in carts
            if ($i >= 3) {
               $response_data = [
                  "mall_id" => $ec_mall_id,
                  "shop_no" => $shop_no,
                  "member_group_no" => "0",
                  "product_discount" => [],
                  "order_discount" => [],
                  "app_discount_info" => [
                     [
                        "no" => 100,
                        "type" => "P",
                        "name" => "discount_sample",
                        "icon" => "http://placehold.it/32x32",
                        "config" => [
                           "value" => $discount_percent,
                           "value_type" => "P",
                           "discount_unit" => "U"
                        ]
                     ]
                  ],
                  "time" => $time,
                  "trace_no" => base64_encode($time),
                  "app_key" => "app_key",
                  "guest_key" => md5($member_id),
               ];

               foreach ($cart_products as $cart_product) {
                  $price = $cart_product['price'];
                  $discount_price = $price * ($discount_percent / 100);
                  $temp[] = [
                     "basket_product_no" => $cart_product['basket_product_no'],
                     "product_no" => $cart_product['product_no'],
                     "quantity" => $cart_product['quantity'],
                     "price" => $price,
                     "option_price" => $cart_product['option_price'],
                     "quantity_based_discount" => 0,
                     "non_quantity_based_discount" => 0,
                     "app_quantity_based_discount" => 0,
                     "app_non_quantity_based_discount" => 30,
                     "variant_code" => $cart_product['variant_code'],
                     "app_product_discount_info" => [
                        [
                           "no" => 100,
                           "price" => $discount_price,
                           "discount_unit" => "I"
                        ]
                     ]
                  ];
               }
               $response_data["product_discount"] = $temp;
               $response_data["hmac"] = base64_encode(hash_hmac('sha256', json_encode($response_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), $this->client_secret , true));
               $result['code'] = 200;
               $result['message'] = "OK";
               $result['data'] = $response_data;
            
            } else {
               $result['message'] = "There is less then 3 discounted product in cart";
            }
            $discount_api_log = [
               "mall_id" => $mall_id,
               "cafe_mall_id" => $ec_mall_id,
               "data" => json_encode($response_data),
               "created_at" => time(),
            ];
            DiscountApiLog::insert($discount_api_log);
         }
      }
      return $result;
   }
}