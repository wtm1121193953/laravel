<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     * 商品统计表
     * @return void
     */
    public function up()
    {
        Schema::create('goods_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('total_count')->default(0)->comment('总库存');
            $table->integer('sell_count')->default(0)->comment('总销量');
            $table->integer('view_count')->default(0)->comment('展示次数');
            $table->integer('collect_count')->default(0)->comment('收藏数');
            $table->integer('comment_count')->default(0)->comment('评论数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_statistics');
    }
}
