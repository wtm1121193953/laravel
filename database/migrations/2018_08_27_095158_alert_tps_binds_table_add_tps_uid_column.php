<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTpsBindsTableAddTpsUidColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('tps_binds', function (Blueprint $table) {
            $table->integer('tps_uid')->index()->default(0)->comment('绑定的TPS账号ID')->after('origin_id');
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
        Schema::table('tps_binds', function (Blueprint $table) {
            $table->dropColumn([
                'tps_uid',
            ]);
        });
    }
}
