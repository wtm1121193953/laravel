<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_follows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->index()->default(0)->comment('商户ID');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('status')->default(1)->comment('1-未关注,2-已关注');
            $table->timestamps();
            $table->comment = '商家关注表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_follows');
    }
}
