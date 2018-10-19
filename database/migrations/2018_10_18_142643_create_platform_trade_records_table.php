<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformTradeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_trade_records', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->default(0)->comment('交易类型 1 支付 2 退款');
            $table->integer('pay_id')->default(0)->comment('支付方式ID');
            $table->decimal('trade_amount',10,2)->default(0.00)->comment('交易金额');
            $table->dateTime('trade_time')->nullable()->comment('交易时间');
            $table->string('trade_no',50)->default('')->comment('交易单号');
            $table->string('order_no',30)->default('')->comment('订单号');
            $table->integer('oper_id')->default(0)->comment('运营中心id');
            $table->integer('merchant_id')->default(0)->comment('商户id');
            $table->integer('user_id')->default(0)->comment('用户id');
            $table->string('remark',100)->default('')->comment('备注');
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
        Schema::dropIfExists('platform_trade_records');
    }
}
