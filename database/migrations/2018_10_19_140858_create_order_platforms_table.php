<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_platforms', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('trade_amount')->default(0)->comment('实收金额');
            $table->integer('trade_count')->default(0)->comment('实收笔数');
            $table->decimal('refund_amount')->default(0)->comment('退款金额');
            $table->integer('refund_count')->default(0)->comment('退款笔数');
            $table->decimal('income')->default(0)->comment('收益');
            $table->integer('merchant_id')->default(0)->comment('商户ID');
            $table->integer('oper_id')->default(0)->comment('运营中心ID');
            $table->dateTime('trade_day')->nullable()->comment('交易日');
            $table->timestamps();

            $table->comment ='交易汇总表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_platforms');
    }
}
