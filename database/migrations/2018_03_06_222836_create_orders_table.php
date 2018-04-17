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
            $table->string('open_id')->default('')->comment('支付用户的微信open_id');
            $table->string('user_name')->default('')->comment('购买用户名');
            $table->string('notify_mobile')->default('')->comment('用户通知手机号');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('merchant_name')->default('')->comment('商家名');
            $table->integer('goods_id')->index()->default(0)->comment('商品ID');
            $table->string('goods_name')->default('')->comment('商品名');
            $table->string('goods_pic', 500)->default('')->comment('商品图片');
            $table->string('goods_thumb_url', 500)->default('')->comment('商品缩略图');
            $table->decimal('price')->default(0)->comment('商品单价');
            $table->integer('buy_number')->default(0)->comment('购买数量');
            $table->tinyInteger('status')->default(0)->comment('状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已支付 5-退款中[保留状态] 6-已退款 7-已完成 (不可退款)');
            // 下单时间见 created_at 字段
            $table->decimal('pay_price')->default(0)->comment('支付金额');
            $table->dateTime('pay_time')->nullable()->comment('付款时间');
            $table->decimal('refund_price')->default(0)->comment('退款金额');
            $table->dateTime('refund_time')->nullable()->comment('退款时间');
            $table->dateTime('finish_time')->index()->nullable()->comment('结束时间, 即核销时间');

            $table->tinyInteger('settlement_status')->index()->default(1)->comment('结算状态 1-未结算 2-已结算');
            $table->integer('settlement_id')->index()->default(0)->comment('结算ID');

            $table->timestamps();
            $table->softDeletes();

            $table->comment = '订单表';
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
