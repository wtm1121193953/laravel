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
