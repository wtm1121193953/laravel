<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/13
 * Time: 0:11
 */

namespace App\Modules\Invite;

use Carbon\Carbon;

/**
 * 邀请记录统计相关服务
 * Class InviteStatisticsService
 * @package App\Modules\Invite
 */
class InviteStatisticsService
{
    public static function getInviteCountByDate($date, $originId, $originType)
    {
        if($date instanceof Carbon){
            $date = $date->format('Y-m-d');
        }
        return InviteUserRecord::whereDate('created_at', $date)
            ->where('origin_id', $originId)
            ->select('origin_type', $originType)
            ->count();
    }
}