<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsStatisticsMerchantOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_statistics_merchant_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cs_merchant_id')->index()->default(0)->comment('超市商户ID');
            $table->string('order_number_30d')->default(0)->comment('最近30天下单数量');
            $table->integer('order_number_today')->default(0)->comment('当天下单数量');
            $table->timestamps();
            $table->comment = '超市商户月订单统计';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_statistics_merchant_orders');
    }
}
