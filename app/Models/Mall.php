<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Models\Log\MallAccessLog;

class Mall extends BaseModel
{
    protected $fillable = [
        "cafe_mall_id",
        "shop_no",
        "mall_name",
        "mall_url",
        "language",
        "is_app_disabled",
        "is_app_deleted",
        "is_app_expired",
        "app_expire_date",
        "access_token",
        "refresh_token",
        "created_at",
        "updated_at",
    ];

    public function accessLogs(){
        $this->hasMany(MallAccessLog::class);
    }

}
