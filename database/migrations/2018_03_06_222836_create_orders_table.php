<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->comment('运营中心ID');
            $table->string('order_no', 100)->index()->comment('订单号');
            $table->integer('user_id')->index()->comment('购买用户ID');
            $table->string('user_name')->index()->comment('购买用户名');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('merchant_name')->default('')->comment('商家名');
            $table->integer('goods_id')->index()->default(0)->comment('商品ID');
            $table->string('goods_name')->default('')->comment('商品名');
            $table->string('goods_pic', 500)->default('')->comment('商品图片');
            $table->decimal('price')->default(0)->comment('商品价格');
            $table->tinyInteger('status')->default(0)->comment('状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已付款 5-退款中 6-已退款 7-已完成 (不可退款)');
            // 下单时间见 created_at 字段
            $table->decimal('pay_price')->default(0)->comment('支付金额');
            $table->timestamp('pay_time')->nullable()->comment('付款时间');
            $table->decimal('refund_price')->default(0)->comment('退款金额');
            $table->timestamp('refund_time')->nullable()->comment('退款时间');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
