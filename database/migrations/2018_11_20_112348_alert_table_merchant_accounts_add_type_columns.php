<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantAccountsAddTypeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchant_accounts', function (Blueprint $table) {
            //
            $table->tinyInteger('type')->default(1)->comment('商户类型 1-普通商户 2-超市商户')->after('merchant_id');
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
        Schema::table('merchant_accounts', function (Blueprint $table) {
            //
            $table->dropColumn([
                'type'
            ]);
        });
    }
}
