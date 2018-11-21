<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsMerchantCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_merchant_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cs_merchant_id')->default(0)->comment('超市商户ID');
            $table->integer('platform_category_id')->default(0)->comment('平台分类ID');
            $table->string('cs_cat_name')->default('')->comment('平台分类名称');
            $table->integer('cs_category_parent_id')->default(0)->comment('平台分类父ID');
            $table->tinyInteger('cs_category_level')->default(0)->comment('平台分类层级');
            $table->tinyInteger('platform_cat_status')->default(0)->comment('平台分类状态');
            $table->tinyInteger('status')->default(0)->comment('分类状态 1上架 2下架');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->comment = '商户商品分类';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_merchant_categories');
    }
}
