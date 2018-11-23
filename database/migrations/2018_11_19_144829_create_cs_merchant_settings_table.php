<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsMerchantSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cs_merchant_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cs_merchant_id')->index()->default(0)->comment('商户id');
            $table->decimal('delivery_start_price')->default(0.00)->comment('起送价');
            $table->decimal('delivery_charges')->default(0.00)->comment('配送费');
            $table->tinyInteger('delivery_free_start')->default(0)->comment('是否开启满多少免运费 1是 0否');
            $table->decimal('delivery_free_order_amount')->default(0.00)->comment('订单满多少免运费');
            $table->timestamps();
            $table->comment = '商户设置表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cs_merchant_settings');
    }
}
