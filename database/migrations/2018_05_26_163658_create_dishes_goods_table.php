<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDishesGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dishes_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('name')->index()->default('')->comment('点菜商品名称');
            $table->decimal('market_price')->default(0)->comment('市场价格');
            $table->decimal('sale_price')->default(0)->comment('销售价格');
            $table->integer('dishes_category_id')->default(0)->comment('分类');
            $table->string('intro')->default('')->comment('商品描述');
            $table->string('detail_image')->default('')->comment('商品详情图片');
            $table->tinyInteger('status')->index()->default(1)->comment('1-上架，2-下架');
            $table->timestamps();
            $table->softDeletes();
            $table->comment = '点菜商品表（对应前端的单品管理）';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dishes_goods');
    }
}
