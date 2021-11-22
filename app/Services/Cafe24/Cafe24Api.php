<?php

namespace App\Services\Cafe24;

use App\Models\Mall;
use App\Facades\Cafe24\Cafe24;
use App\Libs\Cafe24\Cafe24Token;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Cafe24Api {
    protected $method = "get";
    protected $domain;
    protected $admin_api_prefix;
    protected $version;
    protected $client_id;
    protected $client_secret;
    protected $mall_id;

    public function __construct(){
        $this->domain = env('CAFE24_API_DOMAIN');
        $this->admin_api_prefix = env('CAFE24_ADMIN_API_PREFIX');
        $this->version = env('CAFE24_API_VERSION');
        $this->client_id = (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_ID') : env('CAFE24_APP_CLIENT_ID_DEV');
        $this->client_secret = (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_SECRET') : env('CAFE24_APP_CLIENT_SECRET_DEV');;
    }

    /**
     * Use to call Cafe24 API 
     *
     * @param string $mall_id
     * @param string $access_token
     * @param string $end_point
     * @param array $param
     *
     * @return array $mall_info
     */
    public function callApi($mall_id, $access_token, $end_point, $param = array()){
        try {
            $result = [
                "success" => false,
                "data" => [],
                "errors" => [],
                "msg" => "",
            ];

            $is_expired_access_token = false;

            $method = $this->method;
            $version = $this->version;
            $admin_api_prefix = $this->admin_api_prefix;
            $domain = $this->domain;
            
            $url = "https://{$mall_id}.{$domain}/{$admin_api_prefix}/{$end_point}";
            $curl = curl_init();


            $curl_opts = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer {$access_token}",
                    "Content-Type: application/json",
                    "X-Cafe24-Api-Version: {$version}",
                    "X-Cafe24-Api-Spec-Cache: off",
                ),
            ];

            if (count($param) > 0) {
                switch ($method) {
                    case "POST":
                    case "PUT":
                    case "DELETE":
                        $curl_opts[CURLOPT_POSTFIELDS] = json_encode($param);
                        break;
                        
                    case "GET":
                        $curl_opts[CURLOPT_URL] = $url . "?" . http_build_query($param);
                        break;
                }
            }

            curl_setopt_array($curl, $curl_opts);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            if ($err) {
                $error_msg = $err;
                $result["success"] = false;
                $result["errors"] = $error_msg;
                Log::error($error_msg);
                return $result;
            } else {
                $res = json_decode($response);
                if (!empty($res->error)) {
                    if (strpos($res->error->message, "Invalid access_token") > -1) {                //wrong access_token
                        $result["success"] = false;
                        $result["msg"] = $res->error->message;
                        return $result;
                    } else if (strpos($res->error->message, "access_token time expired.") > -1) {          //access_token expired
                        /* refresh token */
                        $mall = Mall::where(["cafe_mall_id" => $mall_id])->first();
                        if (!empty($mall)) {
                            $app_setting = [
                                "refresh_token" => $mall->refresh_token,
                                "client_id" => $this->client_id,
                                "client_secret" => $this->client_secret,
                                "mall_id" => $mall_id,
                            ];
    
                            $cafe24NewToken = Cafe24Token::refreshToken($app_setting);
                            //if (!empty($cafe24NewToken->error_description) || (isset($cafe24NewToken["error"]) && !empty($cafe24NewToken["error"]))){
                            if (isset($cafe24NewToken->error_description)){
                                $error_msg = !empty($cafe24NewToken->error_description) ? $cafe24NewToken->error_description : $cafe24NewToken["error"];
                                $result["success"] = false;
                                $result["msg"] = $error_msg;
                                Log::error($error_msg);
                                return $result;
                            } else {
                                // update token for Mall
                                $access_token = $cafe24NewToken->access_token;
                                $refresh_token = $cafe24NewToken->refresh_token;
                                Cafe24::updateMallToken($cafe24NewToken->mall_id, $access_token, $refresh_token);
                                
                                $is_expired_access_token = true;
                            }
                        }
                    } else {
                        $error_code = $res->error->code;
                        $error_msg = $res->error->message;
                        $result["success"] = false;
                        $result["code"] = $error_code;
                        $result["msg"] = $error_msg;
                        Log::error($error_msg);
                        return $result;
                    }
                } else {
                    $result["data"] = $res;
                    $result["success"] = true;
                }
            }

            
            if ($is_expired_access_token) {         /* recall Cafe24 API */
                return $this->callApi($mall_id, $access_token, $end_point, $param);
            } else {
                return $result;
            }
        } catch (Exception $e) {
            report($e);
            Log::error($e->getMessage());
            return response()->json([
                "success" => false,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
                "msg" => $e->getMessage(),
            ]);
        }
    }

    /**
     * Method setMethod
     *
     * @param string $method
     */
    public function setMethod($method){
        $this->method = $method;
    }

    /**
     * Method get
     *
     * @param string $mall_id
     * @param string $access_token
     * @param string $end_point
     * @param array $param
     *
     * @return void
     */
    public function get($mall_id, $access_token, $end_point, $param = array()){
        $this->method = "GET";
        $result = $this->callApi($mall_id, $access_token, $end_point, $param);
        return $result;
    }

    /**
     * Method post
     *
     * @param string $mall_id
     * @param string $access_token
     * @param string $end_point
     * @param array $param
     *
     * @return void
     */
    public function post($mall_id, $access_token, $end_point, $param = array()){
        $this->method = "POST";
        $result = $this->callApi($mall_id, $access_token, $end_point, $param);
        return $result;
    }

    /**
     * Method put
     *
     * @param string $mall_id
     * @param string $access_token
     * @param string $end_point
     * @param array $param
     *
     * @return array $result
     */
    public function put($mall_id, $access_token, $end_point, $param = array()){
        $this->method = "PUT";
        $result = $this->callApi($mall_id, $access_token, $end_point, $param);
        return $result;
    }


    /**
     * Method delete
     *
     * @param string $mall_id
     * @param string $access_token
     * @param string $end_point
     * @param array $param
     *
     * @return array $result
     */
    public function delete($mall_id, $access_token, $end_point, $param = array()){
        $this->method = "delete";
        $result = $this->callApi($mall_id, $access_token, $end_point, $param);
        return $result;
    }
}