<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsSpecsTable extends Migration
{
    /**
     * Run the migrations.
     * 商品规格表
     * @return void
     */
    public function up()
    {
        Schema::create('goods_specs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goods_id')->index()->comment('商品ID');
            $table->string('spec_1')->default('')->comment('商品规格1的值');
            $table->string('spec_2')->default('')->comment('商品规格2的值');
            $table->decimal('purchase_price')->default(0)->comment('采购价');
            $table->decimal('origin_price')->default(0)->comment('商品售价');
            $table->decimal('discount_price')->default(0)->comment('商品折扣价格 [暂时不用]');
            $table->integer('stock')->default(0)->comment('库存');
            $table->string('sku')->default(0)->comment('SKU');
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
        Schema::dropIfExists('goods_specs');
    }
}
