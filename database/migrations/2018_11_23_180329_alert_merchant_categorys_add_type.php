<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertMerchantCategorysAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('merchant_categories', function (Blueprint $table) {
            //
            $table->integer('type')->default(2)->comment('跳转方式')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('merchant_categories', function (Blueprint $table) {
            //
            $table->dropColumn([
                'type'
            ]);
        });
    }
}
