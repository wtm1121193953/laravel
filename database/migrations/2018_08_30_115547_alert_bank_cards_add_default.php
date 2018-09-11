<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AlertBankCardsAddDefault
 * Author:Jerry
 * Date: 180830
 */
class AlertBankCardsAddDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_cards', function (Blueprint $table) {
           $table->tinyInteger('default')->default(0)->comment('默认使用： 0 为非默认、 1 为默认');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_cards', function (Blueprint $table) {
            $table->dropColumn([
                'default'
            ]);
        });
    }
}
