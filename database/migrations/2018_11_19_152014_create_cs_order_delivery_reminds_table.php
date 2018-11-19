<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsOrderDeliveryRemindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_order_delivery_reminds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no',50)->default('')->comment('订单号');
            $table->integer('cs_merchant_id')->default(0)->comment('商户ID');
            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->integer('remind_times')->default(0)->comment('提醒次数');
            $table->timestamps();
            $table->comment = '发货提醒表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_order_delivery_reminds');
    }
}
