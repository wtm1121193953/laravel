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
            $table->integer('cs_merchant_id')->default(0)->comment('超市商户ID');
            $table->string('month',10)->default('')->comment('统计月份');
            $table->integer('order_number')->default(0)->comment('下单数量');
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
