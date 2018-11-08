<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOpersTableAddNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('opers', function (Blueprint $table) {
            $table->string('number',30)->default('')->comment('运营中心编码')->after('name');
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
        Schema::table('opers', function (Blueprint $table) {
            $table->dropColumn([
                'number'
            ]);
        });
    }
}
