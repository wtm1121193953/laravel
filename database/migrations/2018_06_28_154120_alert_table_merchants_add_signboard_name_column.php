<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableMerchantsAddSignboardNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('merchants', function (Blueprint $table){
            $table->string('signboard_name')->default('')->comment('商家招牌名称')->after('brand');
        });

        DB::statement('update merchants as a set a.signboard_name = a.name');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('merchants', function (Blueprint $table){
            $table->dropColumn([
                'signboard_name'
            ]);
        });
    }
}
