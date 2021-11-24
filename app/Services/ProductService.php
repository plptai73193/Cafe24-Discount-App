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

      // return json_decode($params['product']);
      //Validate field
      $require_field = [
         "mall_id",
         "shop_no",
         // "member_id",
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
      $cart_products = json_decode($params["product"]);
      $shop_no = $params["shop_no"];
      $member_id = $params["member_id"];
      $group_no = $params["group_no"];
      $time = $params["time"];
      $discount_percent = 30;


      // Get mall token
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
         // Get Product ID from cart
         foreach ($cart_products as $cart_product) {
            $cart_product_quantity = $cart_product->quantity;
            if ($cart_product_quantity > 1) {
               for ($i = 1; $i <= $cart_product_quantity; $i++) {
                  $cart_product_nos[] = $cart_product->product_no;
               }
            } else {
               $cart_product_nos[] = $cart_product->product_no;
            }
         }


         // Get Product ID from healthy-juice category
         $endpoint_products = "products";
         $cf_params = [
            "shop_no" => $shop_no,
            "category" => 64,
            "limit" => 100,
         ];
         // $res_data = Cafe24Api::get($ec_mall_id, $access_token, $endpoint_products, $cf_params);
         // if ($res_data['success'] == true && !empty($res_data['data']->products)) {
         //    $products = $res_data['data']->products;
         //    $category_product_array = [];
         //    foreach ($products as $product) {
         //       $category_product_array[] = $product->product_no;
         //    }
            $category_product_array =[ 
               193,
               192,
               189,
               187,
               180,
               179,
               178,
               163,
               158,
               154,
               133,
               100,
               72,
               71,
               57,
               55,
               54,
               53,
               32,
               30,
            ];

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
                  "member_id" => $member_id,
                  "member_group_no" => "0",
                  "product_discount" => [],
                  "order_discount" => [
                     [
                        "no" => "200",
                        "price" => "1000",
                        "apply_product" => ""
                     ]
                  ],
                  "app_discount_info" => [
                     [
                        "no" => 100,
                        "type" => "P",
                        "name" => "discount_sample",
                        "icon" => "http://placehold.it/32x32",
                        "config" => [
                           "value" => $discount_percent,
                           "value_type" => "P",
                           // "discount_unit" => "U"
                        ]
                     ]
                     
                  ],
                  "time" => $time,
                  "trace_no" => base64_encode($time),
                  "app_key" => "rPfg9Eje6UOzGsnY0rD5wD",
                  
               ];

               foreach ($cart_products as $cart_product) {
                  $price = $cart_product->product_price;
                  $discount_price = $price * ($discount_percent / 100);
                  $products_varian_code = $cart_product->item_code . "," . $cart_product->item_code;
                  $temp[] = [
                     "basket_prd_no" => $cart_product->basket_prd_no,
                     "product_no" => $cart_product->product_no,
                     "item_code" => $cart_product->item_code,
                     "product_qty" => $cart_product->quantity,
                     "product_price" => $price,
                     "opt_price" => $cart_product->opt_price,
                     "product_sale_price" => $price,
                     "discount_price" => $discount_price,
                     "discount_info" => [],
                     // "quantity_based_discount" => 0,
                     // "non_quantity_based_discount" => 0,
                     // "app_quantity_based_discount" => 0,
                     // "app_non_quantity_based_discount" => 30,
                     
                     // "app_product_discount_info" => [
                     //    [
                     //       "no" => 100,
                     //       "price" => $discount_price,
                     //       "discount_unit" => "I"
                     //    ]
                     // ]
                  ];
               }
               $response_data["product_discount"] = $temp;
               $response_data["order_discount"][0]['apply_product'] = $products_varian_code;
               $response_data["guest_key"] = md5($member_id);




               // Sample response_data
               /* $sample_data = [
                  "mall_id" => $ec_mall_id,
                  "shop_no" => $shop_no,
                  "member_id" => $member_id,
                  // "mall_id"=>"cafe24_mall",
                  // "shop_no"=>1,
                  // "member_id"=>"",
                  "member_group_no"=> 1,
                  "product_discount"=>[],
                  "order_discount"=>[
                     [
                           "no"=>"200",
                           "price"=>"1000",
                           "apply_product"=>"P000000U000A"
                     ]
                  ],
                  "app_discount_info" => [
                     [
                        "no" => 100,
                        "type" => "P",
                        "name" => "discount_sample",
                        "icon" => "http://placehold.it/32x32",
                        "config" => [
                           "value" => $discount_percent,
                           "value_type" => "P",
                           // "discount_unit" => "U"
                        ]
                     ]
                     
                  ],
                  "time"=>$time,
                  "trace_no"=> base64_encode($time),
                  "app_key"=>"rPfg9Eje6UOzGsnY0rD5wD",
                  "guest_key"=>""
               ];
               $sample_data["product_discount"] = $temp;
               $sample_data["order_discount"][0]['apply_product'] = $products_varian_code;
               $sample_data["guest_key"] = md5($member_id); */








               // parse HMAC based on $response_data["product_discount"]


               // Service Key
               // $response_data["hmac"] = base64_encode(hash_hmac('sha256', json_encode($response_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),"SbUW+dC26sGcmdoaWuJZ9Qihm2PhEsgw6UrbvrTbCeE=", true));
               
               $plain_text = json_encode($response_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);




               
               // $plain_text = json_encode($sample_data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
               //Secret key
               $response_data["hmac"] = base64_encode(hash_hmac('sha256', $plain_text ,$this->client_secret, true));




               // $sample_data["hmac"] = base64_encode(hash_hmac('sha256', $plain_text ,"SbUW+dC26sGcmdoaWuJZ9Qihm2PhEsgw6UrbvrTbCeE=", true));
               // $sample_data["hmac"] = "zzzzzzzzzzzzzzzzzzzzzzzzzzzz";

               unset($response_data['guest_key']);

               $result['code'] = 200;
               $result['message'] = "OK";
               $result['data'] = $response_data;
               // $result['data'] = $sample_data;
            
            } else {
               $result['message'] = "There is less than 3 discounted products in cart";
               $response_data = [];
            }
            $discount_api_log = [
               "mall_id" => $mall_id,
               "cafe_mall_id" => $ec_mall_id,
               "data" => json_encode($response_data),
               "created_at" => time(),
            ];
            DiscountApiLog::insert($discount_api_log);
         // }
      }
      return $result;
   }
}