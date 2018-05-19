<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrdersTableAddTypeAndPayTargetType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('type')->index()->default(1)->comment('订单类型 1-团购订单 2-扫码支付订单 3-点菜订单')->after('merchant_name');
            $table->tinyInteger('pay_target_type')->index()->default(1)->comment('支付目标类型  1-支付给运营中心 2-支付给平台')->after('pay_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'pay_target_type',
            ]);
        });
    }
}
