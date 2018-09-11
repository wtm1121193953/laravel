<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrdersTableAddChargeAndRealAmountColumn extends Migration
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
            $table->decimal('settlement_charge_amount', 8, 2)->default(0)->comment('该笔订单的结算手续费( = pay_price * settlement_rate / 100)')->after('settlement_id');
            $table->decimal('settlement_real_amount', 8, 2)->default(0)->comment('该笔订单的货款 ( = pay_price - settlement_charge_amount )')->after('settlement_id');
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
                'settlement_charge_amount',
                'settlement_real_amount',
            ]);
        });
    }
}
