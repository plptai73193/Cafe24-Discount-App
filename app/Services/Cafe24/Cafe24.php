<?php

namespace App\Services\Cafe24;

use App\Models\Mall;

class Cafe24 {
    protected $mall_id;
    protected $cafe24Param;
    
    /**
     * Use to save {id, cafe_mall_id, access_token, refresh_token}
     *
     * @param array $cafe24Param
     */
    public function setCafe24Token($cafe24Param){
        $this->cafe24Param = $cafe24Param;
    }


    /**
     * Get cafe24's param {id, cafe_mall_id, access_token, refresh_token}
     *
     * @param array $cafe24Param
     * 
     * @return array $cafe24Param
     */
    public function getCafe24Token(){
        return $this->cafe24Param;
    }

    
    /**
     * Update token for mall
     *
     * @param string $cafe_mall_id
     * @param string $access_token
     * @param string $refresh_token
     *
     * @return boolean
     */
    public function updateMallToken($cafe_mall_id, $access_token, $refresh_token){
        $mall = Mall::where("cafe_mall_id", $cafe_mall_id)->update([
            "access_token" => $access_token,
            "refresh_token" => $refresh_token,
        ]);
        return true;
    }
}