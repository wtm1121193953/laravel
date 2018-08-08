<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantAddIsPilotColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchants', function (Blueprint $table) {
            $table->tinyInteger('is_pilot')->index()->default(0)->comment('是否是试点商户 0普通商户 1试点商户');
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
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'is_pilot'
            ]);
        });
    }
}
