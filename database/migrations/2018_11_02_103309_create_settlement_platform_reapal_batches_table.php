<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementPlatformReapalBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_platform_reapal_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_no',50)->default('')->comment('批次号');
            $table->string('settlement_platfrom_ids',1500)->default('')->comment('结算单的id');
            $table->text('data_send')->comment('请求的报文');
            $table->text('data_receive')->comment('接收到的报文');
            $table->text('data_query')->comment('查询结果');
            $table->integer('total')->default(0)->comment('结算单总数');
            $table->integer('success')->default(0)->comment('受理成功数');
            $table->integer('fail')->default(0)->comment('受理失败数');
            $table->integer('pay_success')->default(0)->comment('打款成功数');
            $table->integer('pay_fail')->default(0)->comment('打款失败数');
            $table->timestamp('send_time')->comment('发送时间');
            $table->tinyInteger('status')->default(0)->comment('结算单处理状态 0 未处理 1 已推送 2 处理完成');
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
        Schema::dropIfExists('settlement_platform_reapal_batches');
    }
}
