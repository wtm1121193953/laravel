<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMiniprogramScenesTableAddMerchantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('miniprogram_scenes', function (Blueprint $table) {
            $table->tinyInteger('merchant_id')->index()->default(0)->comment('所属商户ID')->after('oper_id');
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
        Schema::table('miniprogram_scenes', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_id',
            ]);
        });
    }
}
