<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/5/7
 * Time: 14:04
 */

namespace App\Observers;


use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\User\UserOpenIdMapping;

class UserOpenIdMappingObserver
{

    public function created(UserOpenIdMapping $mapping)
    {
        // 用户创建时为用户生成推广渠道信息
        InviteChannelService::createInviteChannel($mapping->user_id, InviteChannel::ORIGIN_TYPE_USER, $mapping->oper_id);
    }
}