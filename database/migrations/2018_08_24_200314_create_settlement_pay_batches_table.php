<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettlementPayBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settlement_pay_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_no')->index()->default('')->comment('打款批次号');
            $table->tinyInteger('type')->index()->default(1)->comment('状态 1-融宝打款批次 ');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-未提交 2-已提交');
            $table->timestamps();

            $table->comment = '结算自动打款批次表';
        });

        Schema::table('settlement_platforms', function (Blueprint $table) {
            $table->integer('settlement_pay_batch_id')->index()->default(0)->comment('打款批次ID')->after('status');
            $table->string('pay_batch_no')->index()->default('')->comment('打款批次号, 对应 settlement_pay_batches 表 batch_no 字段')->after('settlement_pay_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settlement_pay_batches');

        Schema::table('settlement_platforms', function (Blueprint $table) {
            $table->dropColumn([
                'batch_no'
            ]);
        });
    }
}
