<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOperStatisticsTableAddSomeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oper_statistics', function (Blueprint $table) {
            $table->integer('merchant_invite_num')->default(0)->comment('运营中心商户邀请会员数量')->after('user_num');
            $table->integer('oper_and_merchant_invite_num')->default(0)->comment('运营中心及商户共邀请会员数')->after('merchant_invite_num');
            $table->integer('merchant_pilot_num')->default(0)->comment('试点商户数')->after('merchant_num');
            $table->integer('merchant_total_num')->default(0)->comment('商户总数')->after('merchant_pilot_num');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oper_statistics', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_invite_num',
                'oper_and_merchant_invite_num',
                'merchant_pilot_num',
                'merchant_total_num',
            ]);
        });
    }
}
