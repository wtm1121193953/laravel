<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertSettlementPlatformsTableAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_platforms', function (Blueprint $table) {
            $table->integer('type')->default(1)->comment('结算类型 1-手动打款，2-融宝代付')->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settlement_platforms', function (Blueprint $table) {
            $table->dropColumn([
                'type',
            ]);
        });
    }
}
