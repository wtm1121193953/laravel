<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_pays', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->index()->comment('订单ID');
            $table->string('order_no', 100)->index()->comment('订单号');
            $table->string('transaction_no')->index()->comment('交易单号');
            $table->decimal('amount')->index()->comment('交易金额');
            $table->timestamps();

            $table->comment = '订单支付记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_pays');
    }
}
