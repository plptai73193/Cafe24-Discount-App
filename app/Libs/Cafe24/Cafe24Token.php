<?php

namespace App\Libs\Cafe24;

class Cafe24Token {
    
    /**
     * Method installApp
     * 
     * Run first time to install application
     * 
     * @param array $app_setting (need params: mall_id, client_id, redirect_uri, state, scope)
     *
     * @return void
     */
    public static function installApp($app_setting) {
        extract($app_setting);
        if (!empty($mall_id)) {
            $default_scope = "mall.read_application,mall.write_application";
            if (is_array($scope)) {
                $scope = join(",", $scope);
            }
            if (!empty($scope)) {
                $scope .= ",";
            }
            $scope .= "{$default_scope}";
            $url_get_code = "https://{$mall_id}.cafe24api.com/api/v2/oauth/authorize?response_type=code&client_id={$client_id}&state={$state}&redirect_uri={$redirect_uri}&scope={$scope}";
            header("Location: {$url_get_code}");
        }
    }
    
    /**
     * Method getInstalledMallId
     * 
     * Get mall_id installed application
     *
     * @param array $http_request (get from $_SERVER["HTTP_REFERER"])
     *
     * @return string $mall_id_installed
     */
    public static function getInstalledMallId($http_request) {
        $mall_id_installed = "";
        if (isset($http_request) && strpos($http_request, ".cafe24shop.com") > -1) {
            $mall_id_installed = explode(".cafe24shop.com", $http_request)[0];
            $mall_id_installed = explode("//", $mall_id_installed)[1];
        }

        return $mall_id_installed;
    }

    
    /**
     * Method getToken
     * 
     * Get mall's information: [access_token, refresh_token , client_id , mall_id , scopes]
     * 
     * @param array $app_setting (need params: mall_id, client_id, client_secret, redirect_uri, code, scope)
     *
     * @return array $data
     */
    public static function getToken($app_setting) {
        extract($app_setting);

        $data = [];

        $curl = curl_init();
        
        $param = "grant_type=authorization_code&code={$code}&redirect_uri={$redirect_uri}";
        $base64_encode = base64_encode("{$client_id}:{$client_secret}");
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://{$mall_id}.cafe24api.com/api/v2/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic {$base64_encode}",
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            echo 'cURL Error #:' . $err;
            $data["error"] = $err;
        } else {
            $data = json_decode($response);
        }
        return $data;
    }

    /**
     * Method refreshToken
     * 
     * Get mall's information: [refresh_token , client_id , client_secret , mall_id]
     * 
     * @param string $app_setting
     *
     * @return array $data
     */
    public static function refreshToken($app_setting) {
        extract($app_setting);
        $data = [];

        $curl = curl_init();
        
        $param = "grant_type=refresh_token&refresh_token={$refresh_token}";
        $base64_encode = base64_encode("{$client_id}:{$client_secret}");
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://{$mall_id}.cafe24api.com/api/v2/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic {$base64_encode}",
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            echo 'cURL Error #:' . $err;
            $data["error"] = $err;
        } else {
            $data = json_decode($response);
        }
        return $data;
    }
    
    /**
     * Method getError
     * 
     * Get Cafe24's error returned
     *
     * @param object $response
     * @return string $error_msg
     */
    public static function getError($response) {
        $error_msg = "";
        $response = (array) $response;
        if (!empty($response["error"])) {
            $error = $response["error"];
            $error_desc = $response["error_description"];
            $error_msg = "<b>{$error}</b>: {$error_desc}" ;
        }
        return $error_msg;
    }


    /**
     * Method isExistedId
     * 
     * Check if mall_id existed or not
     *
     * @param string $mall_id
     *
     * @return bool
     */
    public static function isExistedId($mail_id) {
        $data = [];

        $curl = curl_init();
        
        $param = [
            "userId" => $mail_id
        ];
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://user.cafe24.com/vn/join/?action=checkEnableId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $param,
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            echo 'cURL Error #:' . $err;
            $data["error"] = $err;
        }

        return $response == "false"; //if API return false that means this mall_id is existed
    }
}

