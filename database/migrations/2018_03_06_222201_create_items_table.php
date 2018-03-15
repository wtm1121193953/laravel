<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('商品名');
            $table->tinyInteger('status')->default(1)->comment('状态 1-正常 2-禁用');
            $table->integer('supplier_id')->index()->default(0)->comment('所属供应商ID');
            $table->integer('category_id')->index()->default(0)->comment('商品分类ID');
            $table->string('pict_url', 500)->default('')->comment('商品图片');
            $table->string('detail', 5000)->default('')->comment('商品图文详情');
            $table->string('small_images', 5000)->default('')->comment('商品小图列表 (json格式数组)');
            $table->integer('total_count')->default(0)->comment('总库存');
            $table->integer('sell_count')->default(0)->comment('总销量');
            $table->decimal('origin_price')->default(0)->comment('商品原价');
            $table->decimal('discount_price')->default(0)->comment('商品折扣价格');
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
