<?php

namespace App\Models\Logs;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Mall;

class DiscountApiLog extends BaseModel
{
    protected $fillable = [
        "mall_id",
        "cafe_mall_id",
        "data",
        "created_at",
    ];

    public function mall(){
        $this->belongsTo(Mall::class);
    }
}
