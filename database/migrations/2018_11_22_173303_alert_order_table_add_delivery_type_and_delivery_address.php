<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrderTableAddDeliveryTypeAndDeliveryAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->integer('delivery_type')->default(0)->comment('配送方式 0无 1自提 2商家配送')->after('pay_type');
            $table->integer('delivery_address_id')->default(0)->comment('配送地址')->after('delivery_type');
            $table->string('cs_goods_buy')->default('')->comment('购买的超市商品json')->after('dishes_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn([
                'delivery_type',
                'delivery_address_id',
                'cs_goods_buy'
            ]);
        });
    }
}
