<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantDraftsTableBankInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_drafts', function (Blueprint $table) {
            $table->string('bank_name')->default('')->comment('开户行')->after('bank_card_no');
            $table->string('bank_province')->default('')->comment('开户行省份')->after('sub_bank_name');
            $table->integer('bank_province_id')->default(0)->comment('开户行省份id')->after('bank_province');
            $table->string('bank_city')->default('')->comment('开户行城市')->after('bank_province_id');
            $table->integer('bank_city_id')->default(0)->comment('开户行城市id')->after('bank_city');
            $table->string('bank_area')->default('')->comment('开户行县区')->after('bank_city_id');
            $table->integer('bank_area_id')->default(0)->comment('开户行县区id')->after('bank_area');
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
                'bank_name',
                'bank_province',
                'bank_province_id',
                'bank_city',
                'bank_city_id',
                'bank_area',
                'bank_area_id',
            ]);
        });
    }
}
