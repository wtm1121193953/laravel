<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index()->comment('统计日期');
            $table->integer('merchant_id')->index()->default(0)->comment('商户ID');
            $table->integer('oper_id')->index()->default(0)->comment('运营中心ID');
            $table->integer('invite_user_num')->default(0)->comment('商户邀请会员数量');
            $table->decimal('order_finished_amount', 11, 2)->default(0)->comment('订单总金额');
            $table->integer('order_finished_num')->default(0)->comment('订单笔数');
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
        Schema::dropIfExists('merchant_statistics');
    }
}
