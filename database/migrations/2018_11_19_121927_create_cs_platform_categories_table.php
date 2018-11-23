<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsPlatformCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_platform_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cat_name')->default('')->comment('分类名称');
            $table->tinyInteger('status')->index()->default(1)->comment('分类状态 1启用 2暂停');
            $table->integer('parent_id')->index()->default(0)->comment('上级分类ID');
            $table->tinyInteger('level')->default(1)->comment('分类层级');
            $table->timestamps();
            $table->comment = '平台商品分类';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_platform_categories');
    }
}
