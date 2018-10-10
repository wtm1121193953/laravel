<?php

namespace App\Listeners;

use App\Events\InviteUserRecordsCreatedEvent;
use App\Modules\Oper\OperStatistics;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Invite\InviteUserRecord;

/**
 * 运营平台新增用户增量计算
 * Class OperStatisticsAddUserListener
 * Author:   JerryChan
 * Date:     2018/9/20 16:00
 * @package App\Listeners
 */
class OperStatisticsAddUserListener
{
    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param  InviteUserRecordsCreatedEvent $event
     * @return void
     */
    public function handle( InviteUserRecordsCreatedEvent $event )
    {
        // 判断邀请渠道不为为运营平台 或者origin_id非法
        if ($event->inviteUserRecord->origin_type != InviteUserRecord::ORIGIN_TYPE_OPER || !$event->inviteUserRecord->origin_id) {
            return;
        }
        // 添加对应的平台用户量
        OperStatistics::where('oper_id', $event->inviteUserRecord->origin_id)
                        ->where('date', $event->date)
                        ->increment('user_num');
    }
}
