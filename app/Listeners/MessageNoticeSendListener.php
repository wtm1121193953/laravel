<?php

namespace App\Listeners;

use App\Events\InviteUserRecordCreatedEvent;
use App\Events\UserCreatedEvent;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteUserRecord;
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
     * @param InviteUserRecordCreatedEvent $event
     * @return void
     */
    public function handle(InviteUserRecordCreatedEvent $event)
    {
        $inviteUserRecord =  $event->record;
        if($inviteUserRecord->origin_type!=InviteChannel::ORIGIN_TYPE_USER){
            // 邀请渠道不为用户类型，直接退出
            return;
        }
        $user = UserService::getUserById($inviteUserRecord->user_id);
        if(!$user){
            return ;
        }
        $inviteTime = strtotime($inviteUserRecord->updated_at);
        $userTime = strtotime($user->created_at);
        if(($inviteTime-$userTime)>60){
            // 如果不是同一时间存在的数据，不发送
            return;
        }
        MessageNoticeService::createByRegister($user->mobile,$inviteUserRecord->origin_id);
    }
}
