<?php

use App\Modules\Invite\InviteChannel;
use App\Modules\Wechat\MiniprogramScene;
use Illuminate\Support\Collection;
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
        Schema::table('miniprogram_scenes', function (Blueprint $table) {
            $table->integer('invite_channel_id')->index()->default(0)->comment('小程序场景码关联的邀请渠道ID')->after('merchant_id');
        });

        InviteChannel::where('scene_id', '>', 0)
            ->chunk(1000, function(Collection $list){
                $list->each(function(InviteChannel $item){
                    $sceneId = $item->scene_id;
                    $scene = MiniprogramScene::find($sceneId);
                    $scene->invite_channel_id = $item->id;
                    $scene->save();
                });
            });

        Schema::table('invite_channels', function (Blueprint $table){
            $table->dropColumn([
                'scene_id',
            ]);
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
            $table->integer('scene_id')->index()->default(0)->comment('场景ID')->after('origin_type');
        });

        MiniprogramScene::where('invite_channel_id', '>', 0)
            ->chunk(1000, function(Collection $list){
                $list->each(function(MiniprogramScene $item){
                    $invite_channel_id = $item->invite_channel_id;
                    $invite_channel = InviteChannel::find($invite_channel_id);
                    $invite_channel->scene_id = $item->id;
                    $invite_channel->save();
                });
            });

        Schema::table('miniprogram_scenes', function (Blueprint $table){
            $table->dropColumn([
                'invite_channel_id',
            ]);
        });
    }
}
