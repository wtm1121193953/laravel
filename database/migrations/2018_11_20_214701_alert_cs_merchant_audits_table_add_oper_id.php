<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertCsMerchantAuditsTableAddOperId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cs_merchant_audits', function (Blueprint $table) {
            //
            $table->integer('oper_id')->default(0)->comment('运营中心id')->after('id');
            $table->string('name')->default('')->comment('商户名称')->after('cs_merchant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cs_merchant_audits', function (Blueprint $table) {
            //
            $table->dropColumn([
                'oper_id',
                'name'
            ]);
        });
    }
}
