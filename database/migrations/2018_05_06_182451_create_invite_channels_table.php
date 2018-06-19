<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('运营中心ID, 由于渠道对应的是运营中心小程序, 每个用户对应每个运营中心都会有一个推广渠道');
            $table->integer('origin_id')->index()->default(0)->comment('推广人ID(用户ID, 商户ID 或 运营中心ID)');
            $table->tinyInteger('origin_type')->index()->default(0)->comment('推广人类型  1-用户 2-商户 3-运营中心');
            $table->integer('scene_id')->index()->default(0)->comment('场景ID (miniprogram_scenes表id)');

            $table->timestamps();

            $table->comment = '邀请渠道表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invite_channels');
    }
}
