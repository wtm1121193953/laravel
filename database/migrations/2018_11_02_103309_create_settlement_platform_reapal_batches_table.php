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
            $table->string('batch_no',50)->comment('批次号');
            $table->string('settlement_platfrom_ids',1500)->comment('结算单的id');
            $table->text('data_send')->comment('请求的报文');
            $table->text('data_receive')->comment('接收到的报文');
            $table->text('data_query')->comment('查询结果');
            $table->integer('total')->comment('结算单总数');
            $table->integer('success')->comment('受理成功数');
            $table->integer('fail')->comment('受理失败数');
            $table->integer('pay_success')->comment('打款成功数');
            $table->integer('pay_fail')->comment('打款失败数');
            $table->timestamp('send_time')->comment('发送时间');
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
