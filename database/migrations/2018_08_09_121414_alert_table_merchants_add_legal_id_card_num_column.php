<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantsAddLegalIdCardNumColumn extends Migration
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
            $table->string('legal_id_card_num')->default('')->comment('法人身份证号码')->after('legal_id_card_pic_b');
        });
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->string('legal_id_card_num')->default('')->comment('法人身份证号码')->after('legal_id_card_pic_b');
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
                'legal_id_card_num'
            ]);
        });
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->dropColumn([
                'legal_id_card_num'
            ]);
        });
    }
}
