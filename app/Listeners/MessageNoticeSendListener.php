<?php

namespace App\Listeners;

use App\Events\InviteUserRecordsCreatedEvent;
use App\Modules\Invite\InviteChannel;
use App\Modules\Message\MessageNoticeService;
use App\Modules\User\UserService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 发送用户注册通知消息
 * Class MessageNoticeSendListener
 * @package App\Listeners
 */
class MessageNoticeSendListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InviteUserRecordsCreatedEvent  $event
     * @return void
     */
    public function handle(InviteUserRecordsCreatedEvent $event)
    {
        $inviteUserRecord =  $event->inviteUserRecord;
        if($inviteUserRecord!=InviteChannel::ORIGIN_TYPE_USER){
            // 邀请渠道不为用户类型，直接退出
            return;
        }
        $user = UserService::getUserById($inviteUserRecord->user_id);
        MessageNoticeService::createByRegister($user->mobile,$inviteUserRecord->origin_id);
    }
}
