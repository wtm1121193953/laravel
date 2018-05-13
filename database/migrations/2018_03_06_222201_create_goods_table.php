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
            $table->string('desc', 2000)->default('')->comment('商品描述');
            $table->decimal('market_price')->index()->default(0)->comment('商品市场价');
            $table->decimal('price')->index()->default(0)->comment('商品售价');
            $table->date('start_date')->nullable()->comment('商品有效期开始日期');
            $table->date('end_date')->nullable()->comment('商品有效期结束日期');
            $table->string('thumb_url', 500)->default('')->comment('商品缩略图');
            $table->string('pic', 500)->default('')->comment('商品主图');
            $table->string('pic_list', 2000)->default('')->comment('商品图片列表 (逗号分隔字符串)');
            $table->string('buy_info', 1000)->default('')->comment('购买须知');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-上架 2-下架');
            $table->integer('sell_number')->default(0)->comment('已售数量');
            $table->string('ext_attr', 3000)->default('')->comment('商品扩展属性, 如: [name: 重量, value: 5kg]');
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
