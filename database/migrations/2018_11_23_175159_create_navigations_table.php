<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navigations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('position')->default('index')->index()->comment('导航位置 index-首页宫格导航');
            $table->string('title')->default('')->comment('标题');
            $table->string('icon')->default('')->comment('图标');
            $table->string('type')->default('merchant_category')->index()->comment('导航类型 cs_index-超市首页, merchant_category-某个类目商户列表, merchant_category_all-商户类目列表, h5-h5链接');
            $table->string('payload', 1000)->default('')->comment('导航附加数据, json格式字符串');
            $table->integer('sort')->default(0)->comment('排序, 越大的排在越前面');
            $table->timestamps();
            $table->comment = 'app导航表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('navigations');
    }
}
