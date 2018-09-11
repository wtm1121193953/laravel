<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOrderRefundsTableAddRefundNoColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('order_refunds', function (Blueprint $table) {
            $table->string('refund_no')->index()->default('')->comment('退款单号')->after('id');
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
        Schema::table('order_refunds', function (Blueprint $table) {
            $table->dropColumn([
                'refund_no',
            ]);
        });
    }
}
