<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/26
 * Time: 14:52
 */

namespace App\Modules\Wechat;


use App\BaseService;
use App\Modules\Invite\InviteChannel;

class MiniprogramSceneService extends BaseService
{

    /**
     * 根据邀请渠道获取小程序场景信息
     * @param InviteChannel $inviteChannel
     * @return MiniprogramScene
     */
    public static function getByInviteChannel(InviteChannel $inviteChannel): MiniprogramScene
    {
        return self::getByInviteChannelId($inviteChannel->id, $inviteChannel->oper_id);
    }

    /**
     * 根据渠道ID与运营中心ID获取小程序场景信息
     * @param $inviteChannelId
     * @param $operId
     * @return MiniprogramScene
     */
    public static function getByInviteChannelId($inviteChannelId, $operId): MiniprogramScene
    {
        // todo
    }
}