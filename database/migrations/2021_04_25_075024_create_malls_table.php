<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('malls', function (Blueprint $table) {
            $table->id();
			$table->string("cafe_mall_id")->index();
            $table->string("shop_no");
			$table->string("mall_name");
			$table->string("mall_url");
            $table->string("language")->nullable(true);
			$table->string("is_app_disabled")->default("0");			// 0: in-use | 1: deleted
			$table->string("is_app_deleted")->default("0");			// 0: in-use | 1: deleted
			$table->string("is_app_expired")->default("0");			// 0: in-use | 1: expired
			$table->string("app_expire_date")->nullable(true);
			$table->string("access_token")->nullable(true);
			$table->string("refresh_token")->nullable(true);
			$table->string("created_at");
			$table->string("updated_at");
            $table->timestamp('updated_at_datetime')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('malls');
    }
}
