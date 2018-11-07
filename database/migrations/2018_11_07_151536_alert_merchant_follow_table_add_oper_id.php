<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantFollowTableAddOperId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_follows', function (Blueprint $table) {
            $table->integer('oper_id')->default(0)->comment('运营中心id')->after('merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_follows', function (Blueprint $table) {
            $table->dropColumn([
                'oper_id'
            ]);
        });
    }
}
