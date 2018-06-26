<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSellNumberToDishesGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dishes_goods', function (Blueprint $table) {
            $table->integer('sell_number')->default(0)->comment('已销售数量')->after('intro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dishes_goods', function (Blueprint $table) {
            $table->dropColumn([
                'sell_number',
            ]);
        });
    }
}
