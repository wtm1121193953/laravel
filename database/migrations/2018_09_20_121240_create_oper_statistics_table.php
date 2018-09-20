<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oper_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index()->comment('日期');
            $table->integer('oper_id')->default(0)->comment('运营中心ID');
            $table->integer('merchant_num')->default(0)->comment('商户数');
            $table->integer('user_num')->default(0)->comment('邀请用户数');
            $table->integer('order_paid_num')->default(0)->comment('总订单量（已支付）');
            $table->integer('order_refund_num')->default(0)->comment('总退款量');
            $table->decimal('order_paid_amount', 11, 2)->default(0)->comment('总订单金额（已支付）');
            $table->decimal('order_refund_amount', 11, 2)->default(0)->comment('总退款金额');
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
        Schema::dropIfExists('oper_statistics');
    }
}
