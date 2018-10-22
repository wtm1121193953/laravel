<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOpersTableAddBizerDivideColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opers', function (Blueprint $table) {
            $table->decimal('bizer_divide')->default(20)->comment('运营中心业务员分成比例');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opers', function (Blueprint $table) {
            $table->dropColumn([
                'bizer_divide',
            ]);
        });
    }
}
