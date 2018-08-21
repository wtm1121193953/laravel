<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBizerAddNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('bizers', function (Blueprint $table) {
            $table->string('name',128)->default('')->comment('业务员姓名')->after('id');;
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
        Schema::table('bizers', function (Blueprint $table) {
            $table->dropColumn([
                'name'
            ]);
        });
    }
}
