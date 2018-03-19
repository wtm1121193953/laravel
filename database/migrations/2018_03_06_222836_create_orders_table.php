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
            $table->string('order_no', 100)->index()->comment('订单号');
            $table->integer('user_id')->index()->comment('购买用户ID');
            $table->string('user_name')->index()->comment('购买用户名');
            $table->tinyInteger('type')->default(1)->index()->comment('订单类型 1-普通订单 2-聚合父订单 3-聚合子订单');
            $table->integer('goods_id')->index()->default(0)->comment('商品ID');
            $table->string('goods_name')->default('')->comment('商品名');
            $table->string('item_pict_url', 500)->default('')->comment('商品图片');
            $table->decimal('origin_price')->default(0)->comment('商品价格');
            $table->decimal('discount_price')->default(0)->comment('商品折扣价格');
            $table->text('snapshot')->comment('商品快照');
            $table->decimal('freight_price')->default(0)->comment('运费');
            // 物流信息 -> 放另外的物流信息表中
            $table->tinyInteger('status')->default(0)->comment('状态 1-未支付 2-已支付 3-有退款(聚合父订单存在该状态) 4-全部退款');
            // 下单时间见 created_at 字段
            $table->decimal('pay_price')->default(0)->comment('支付金额');
            $table->timestamp('pay_time')->nullable()->comment('付款时间');
            $table->decimal('refund_price')->default(0)->comment('退款金额 (聚合父订单为退款总金额)');
            $table->timestamp('refund_time')->nullable()->comment('退款时间 (聚合父订单为最后退款时间)');
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
