<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertSettlementPlatformsTableAddSettlementCycleType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settlement_platforms', function (Blueprint $table) {
            $table->integer('settlement_cycle_type')->default(0)->comment('结款周期 1-周结 2-半月结 3-月结 4-半年结 5-年结 6-T+1')->after('type');
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
                'settlement_cycle_type',
            ]);
        });
    }
}
