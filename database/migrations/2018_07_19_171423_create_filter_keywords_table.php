<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->string('keyword')->index()->default('')->comment('过滤的关键字');
            $table->tinyInteger('status')->default(1)->comment('状态 1整除 2禁用');
            $table->integer('category_number')->default(0)->comment('用二进制表示 从右到左 团购商品名，单品名称，单品分类名称');
            $table->timestamps();
            $table->comment = '关键字过滤表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filter_keywords');
    }
}
