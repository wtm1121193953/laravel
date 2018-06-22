<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableDishesGoodsAddIshot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dishes_goods', function (Blueprint $table) {
            $table->tinyInteger('is_hot')->default(0)->comment('是否热销')->after('sell_number');
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
                'is_hot',
            ]);
        });
    }
}
