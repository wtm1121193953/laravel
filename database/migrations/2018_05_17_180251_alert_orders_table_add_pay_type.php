<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrdersTableAddPayType extends Migration
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
            $table->tinyInteger('pay_type')->index()->default(1)->comment('支付方式, 1-微信支付 2-支付宝支付')->after('status');
            $table->tinyInteger('origin_app_type')->index()->default(3)->comment('订单来源客户端类型, 1-安卓app 2-iOSApp 3-小程序');
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
                'pay_type',
                'origin_app_type',
            ]);
        });
    }
}
