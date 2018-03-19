<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('商品名');
            $table->integer('supplier_id')->index()->default(0)->comment('所属供应商ID');
            $table->integer('category_id')->index()->default(0)->comment('商品分类ID');
            $table->string('brand')->index()->default('')->comment('商品品牌');
            $table->decimal('purchase_price')->index()->default(0)->comment('采购价');
            $table->decimal('origin_price')->index()->default(0)->comment('商品原价');
            $table->decimal('discount_price')->index()->default(0)->comment('商品折扣价格 [暂时不用]');
            $table->tinyInteger('spec_type')->default(0)->comment('商品规格使用类型, 0-不使用商品规格, 1-使用一个商品规格属性, 2-使用两个商品规格属性');
            $table->string('spec_name_1')->default('颜色')->comment('商品规格1名称, 默认: 颜色');
            $table->string('spec_name_2')->default('颜色')->comment('商品规格2名称, 默认: 尺码');
            $table->integer('default_spec_id')->default(0)->comment('默认的商品规格ID');
            $table->integer('category_id_1')->default(0)->comment('商品一级分类ID');
            $table->integer('category_id_2')->default(0)->comment('商品二级分类ID');
            $table->integer('category_id_3')->default(0)->comment('商品三级分类ID');
            $table->integer('category_id_4')->default(0)->comment('商品四级分类ID');

            $table->string('default_image', 500)->default('')->comment('商品默认图');
            $table->string('ext_attr', 5000)->default('')->comment('商品扩展属性, 如: [name: 重量, value: 5kg]');
            $table->string('small_images', 5000)->default('')->comment('商品小图列表 (逗号分隔字符串)');
            $table->text('detail')->nullable()->comment('商品图文详情');
            $table->tinyInteger('status')->default(1)->comment('状态 1-上架 2-下架');
            $table->string('tags', 191)->index()->default('')->comment('商品标签');
            // 运费 -> 放到物流上  -> 商品中可以加是否免运费
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
        Schema::dropIfExists('items');
    }
}
