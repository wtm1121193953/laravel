<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->comment('运营中心ID');
            $table->integer('merchant_id')->index()->comment('商家ID');
            $table->integer('order_id')->index()->comment('订单ID');
            $table->integer('verify_code')->index()->default(0)->comment('核销码');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-未核销, 2-已核销 3-已退款');
            $table->timestamps();

            $table->comment = '订单核销码记录表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
