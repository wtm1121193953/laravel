<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batch_no')->index()->default('')->comment('批次编号');
            $table->tinyInteger('type')->index()->default(0)->comment('批次类型 1-对公 2-对私');
            $table->tinyInteger('status')->index()->default(0)->comment('批次状态 1-结算中 2-准备打款 3-打款完成');
            $table->decimal('amount', 11, 2)->default(0)->comment('总金额');
            $table->integer('total')->default(0)->comment('总笔数');
            $table->decimal('success_amount', 11, 2)->default(0)->comment('打款成功总金额');
            $table->integer('success_total')->default(0)->comment('打款成功总笔数');
            $table->decimal('failed_amount', 11, 2)->default(0)->comment('打款失败总金额');
            $table->integer('failed_total')->default(0)->comment('打款失败总笔数');
            $table->string('remark')->default('')->comment('备注');
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
        Schema::dropIfExists('wallet_batches');
    }
}
