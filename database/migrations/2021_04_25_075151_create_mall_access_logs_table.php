<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallAccessLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mall_id')->constrained('malls');
            $table->string("cafe_mall_id");
            $table->string("created_at");
            $table->timestamp('created_at_datetime')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_access_logs');
    }
}
