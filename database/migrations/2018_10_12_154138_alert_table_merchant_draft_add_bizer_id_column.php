<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantDraftAddBizerIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->integer('bizer_id')->index()->default(0)->comment('业务员ID')->after('level');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->dropColumn([
                'bizer_id'
            ]);
        });
    }
}
