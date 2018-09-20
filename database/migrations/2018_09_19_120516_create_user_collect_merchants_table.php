<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCollectMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_collect_merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商铺ID');
            $table->tinyInteger('status')->default(1)->comment('1：为收藏，2：为取消收藏');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_collect_merchants');
    }
}
