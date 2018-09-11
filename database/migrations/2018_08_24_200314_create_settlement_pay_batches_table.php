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
            $table->string('batch_count')->default('')->comment('批次总数量');
            $table->tinyInteger('pay_type')->default('1')->comment('记账方式 0-加急 1-普通');
            $table->string('pay_sight')->default('')->comment('付款场景');
            $table->string('batch_amount')->default('')->comment('批次总金额');
            $table->string('platform_merchant_id')->default('')->comment('平台商户号');
            $table->tinyInteger('type')->index()->default(1)->comment('状态 1-融宝打款批次 ');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-未提交 2-已提交');
            $table->string('error_code')->default('')->comment('错误代码');
            $table->text('error_msg')->nullable()->comment('错误信息');
            $table->timestamps();

            $table->comment = '结算自动打款批次表';
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
    }
}
