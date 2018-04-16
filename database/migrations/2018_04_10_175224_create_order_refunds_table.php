<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->index()->comment('订单ID');
            $table->string('order_no', 100)->index()->comment('订单号');
            $table->decimal('amount')->comment('退款金额');
            $table->string('refund_id')->default('')->index()->comment('微信退款单号');
            $table->tinyInteger('status')->default(1)->index()->comment('退款状态 1-未退款 2-已退款');
            $table->timestamps();

            $table->comment = '订单退款记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_refunds');
    }
}
