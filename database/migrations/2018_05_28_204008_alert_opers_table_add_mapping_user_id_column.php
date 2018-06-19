<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertOpersTableAddMappingUserIdColumn extends Migration
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
            $table->integer('mapping_user_id')->index()->default(0)->comment('运营中心关联user_id');
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
                'mapping_user_id',
            ]);
        });
    }
}
