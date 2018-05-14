<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertInviteChannelsTableAddTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('invite_channels', function (Blueprint $table) {
            $table->tinyInteger('type')->index()->default(1)->comment('邀请渠道类型 1-小程序邀请码 2-app邀请码');
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

        Schema::table('invite_channels', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
}
