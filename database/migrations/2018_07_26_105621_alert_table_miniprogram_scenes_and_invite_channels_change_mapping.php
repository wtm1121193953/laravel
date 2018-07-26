<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 修改 invite_channels 表与 miniprogram_scenes 表的关联关系 由多对一改为一对多
 *
 * Class AlertTableMiniprogramScenesAndInviteChannelsChangeMapping
 */
class AlertTableMiniprogramScenesAndInviteChannelsChangeMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
