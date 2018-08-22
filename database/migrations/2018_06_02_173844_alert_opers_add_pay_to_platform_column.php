<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOpersAddPayToPlatformColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('opers', function (Blueprint $table) {
            $table->tinyInteger('pay_to_platform')->index()->default(0)->comment('运营中心下的商家是否支付到平台 0-支付给运营中心自己 1-支付到平台(平台不参与分成) 2-支付到平台(平台参与分成)');
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
        Schema::table('opers', function (Blueprint $table) {
            $table->dropColumn([
                'pay_to_platform',
            ]);
        });
    }
}
