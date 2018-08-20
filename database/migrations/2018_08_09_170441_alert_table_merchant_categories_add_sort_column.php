<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantCategoriesAddSortColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchant_categories', function (Blueprint $table) {
            $table->integer('sort')->index()->default(0)->comment('排序')->after('status');
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
        Schema::table('merchant_categories', function (Blueprint $table){
            $table->dropColumn([
                'sort',
            ]);
        });
    }
}
