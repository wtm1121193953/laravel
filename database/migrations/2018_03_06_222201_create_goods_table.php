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
            $table->integer('oper_id')->index()->default(0)->comment('所属运营中心ID');
            $table->integer('merchant_id')->index()->default(0)->comment('商家ID');
            $table->string('name')->default('')->comment('商品名');
            $table->string('desc')->default('')->comment('商品描述');
            $table->decimal('origin_price')->index()->default(0)->comment('商品价格');
            $table->decimal('price')->index()->default(0)->comment('商品售价');
            $table->string('pic', 500)->default('')->comment('商品默认图');
            $table->string('pic_list', 5000)->default('')->comment('商品小图列表 (逗号分隔字符串)');
            $table->string('ext_attr', 5000)->default('')->comment('商品扩展属性, 如: [name: 重量, value: 5kg]');
            $table->text('detail')->nullable()->comment('商品图文详情');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-上架 2-下架');
            $table->string('tags', 191)->index()->default('')->comment('商品标签');
            $table->timestamps();
            $table->softDeletes();

            $table->comment = '商品表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
