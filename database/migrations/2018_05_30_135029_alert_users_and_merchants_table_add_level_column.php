<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertUsersAndMerchantsTableAddLevelColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('level')->index()->default(1)->comment('用户等级 1-萌新 2-粉丝 3-铁杆 4-骨灰');
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->tinyInteger('level')->index()->default(1)->comment('商户等级 1-签约商户 2-联盟商户 3-品牌商户');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'level',
            ]);
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn([
                'level',
            ]);
        });
    }
}
