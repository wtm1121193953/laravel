<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementPlatformKuaiQianBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_platform_kuai_qian_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_no',50)->default('')->comment('批次号');
            $table->tinyInteger('type')->default(0)->comment('类型 1 自动生成批次，2手动重新打款批次');
            $table->text('settlement_platfrom_ids')->comment('结算单的id');
            $table->mediumText('data_send')->comment('请求的报文');
            $table->mediumText('data_receive')->comment('接收到的报文');
            $table->mediumText('data_query')->comment('查询结果');
            $table->decimal('amount',10,2)->default(0.00)->comment('总金额');
            $table->integer('total')->default(0)->comment('结算单总数');
            $table->integer('success')->default(0)->comment('受理成功数');
            $table->integer('fail')->default(0)->comment('受理失败数');
            $table->integer('pay_success')->default(0)->comment('打款成功数');
            $table->integer('pay_fail')->default(0)->comment('打款失败数');
            $table->date('create_date')->comment('生成日期');
            $table->dateTime('send_time')->comment('推送时间');
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
        Schema::dropIfExists('settlement_platform_kuai_qian_batches');
    }
}
