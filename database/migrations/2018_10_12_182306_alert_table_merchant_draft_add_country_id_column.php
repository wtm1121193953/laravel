<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantDraftAddCountryIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->integer('country_id')->default(1)->comment('国别或地区ID')->after('legal_id_card_pic_b');
            $table->string('corporation_name')->default('')->comment('法人姓名')->after('country_id');
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
                'country_id',
                'corporation_name'
            ]);
        });
    }
}
