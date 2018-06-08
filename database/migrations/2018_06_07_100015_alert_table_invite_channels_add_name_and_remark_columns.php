<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableInviteChannelsAddNameAndRemarkColumns extends Migration
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
            $table->string('name', 50)->default('')->comment('渠道名称');
            $table->string('remark')->default('')->comment('备注');
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
            $table->dropColumn([
                'name',
                'remark',
            ]);
        });
    }
}
