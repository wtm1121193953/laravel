<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_order_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('运营中心ID');
            $table->integer('cs_merchant_id')->index()->default(0)->comment('超市商户ID');
            $table->integer('order_id')->index()->default(0)->comment('订单ID');
            $table->integer('cs_goods_id')->default(0)->comment('超市商品ID');
            $table->decimal('price')->default(0.00)->comment('超市商品价格');
            $table->integer('number')->default(0)->comment('购买数量');
            $table->string('goods_name')->default('')->comment('商品名称');
            $table->timestamps();
            $table->comment = '超市订单商品表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_order_goods');
    }
}
