<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\Wechat\MiniprogramScene;

class InviteChannel extends BaseModel
{
    //推广人类型  1-用户
    const ORIGIN_TYPE_USER = 1;
    //推广人类型  2-商户
    const ORIGIN_TYPE_MERCHANT = 2;
    //推广人类型  3-运营中心
    const ORIGIN_TYPE_OPER = 3;

    /**
     * 生成推广渠道
     * @param $operId
     * @param $originId
     * @param $originType
     * @return InviteChannel
     */
    public static function createInviteChannel($operId, $originId, $originType)
    {
        // 不能重复生成
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        if($inviteChannel) {
            return $inviteChannel;
        }
        $scene = new MiniprogramScene();
        $scene->oper_id = $operId;
        // todo 小程序端邀请注册页面地址
        $scene->page = MiniprogramScene::PAGE_INVITE_REGISTER;
        $scene->type = 2;
        $scene->payload = json_encode([
            'origin_id' => $originId,
            'origin_type' => $originType,
        ]);
        $scene->save();

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $originId;
        $inviteChannel->origin_type = $originType;
        $inviteChannel->scene_id = $scene->id;
        $inviteChannel->save();
        return $inviteChannel;
    }
}
