<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableInviteUserRecordsAddInviteChannelId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('invite_user_records', function (Blueprint $table) {
            $table->integer('invite_channel_id')->index()->default(0)->comment('邀请渠道ID')->after('user_id');
        });
        $inviteChannels = [];
        // 查询所有当前的邀请记录并补充邀请渠道Id
        \App\Modules\Invite\InviteUserRecord::chunk(100, function (\Illuminate\Database\Eloquent\Collection $list) use ($inviteChannels){
            $list->each(function($item) use ($inviteChannels){
                $inviteChannel = $inviteChannels["$item->origin_id-$item->origin_type"] ?? null;
                if(empty($inviteChannel)){
                    $inviteChannel = \App\Modules\Invite\InviteChannel::where('origin_id', $item->origin_id)
                        ->where('origin_type', $item->origin_type)
                        ->first();
                    $inviteChannels["$item->origin_id-$item->origin_type"] = $inviteChannel;
                }
                if($inviteChannel){
                    $item->invite_channel_id = $inviteChannel->id;
                    $item->save();
                }
            });
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
        Schema::table('invite_user_records', function (Blueprint $table) {
            $table->dropColumn([
                'invite_channel_id',
            ]);
        });
    }
}
