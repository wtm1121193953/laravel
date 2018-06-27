<?php

use Jialeo\LaravelSchemaExtend\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDishesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dishes_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->integer('dishes_id')->index()->default(0)->comment('dishes表的id');
            $table->integer('dishes_goods_id')->index()->default(0)->comment('单品id');
            $table->integer('number')->default(0)->comment('商品数量');
            $table->decimal('dishes_goods_sale_price', 10, 2)->default(0)->comment('单品售价');
            $table->string('dishes_goods_detail_image')->default('')->comment('单品图片');
            $table->string('dishes_goods_name')->default('')->comment('单品名称');
            $table->timestamps();
            $table->comment = '订单中的单品列表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dishes_items');
    }
}
