<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformTradeRecordsDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('platform_trade_records_dailies', function (Blueprint $table) {
            $table->increments('id');
            $table->date('sum_date')->comment('统计日期');
            $table->integer('pay_id')->default(0)->comment('支付方式id');
            $table->decimal('pay_amount',10,2)->default(0)->comment('总支付金额');
            $table->integer('pay_count')->default(0)->comment('总支付金额');
            $table->decimal('refund_amount',10,2)->default(0)->comment('总退款金额');
            $table->integer('refund_count')->default(0)->comment('总退款笔数');
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
        Schema::dropIfExists('platform_trade_records_dailies');
    }
}
