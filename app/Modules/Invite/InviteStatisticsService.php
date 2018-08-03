<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/13
 * Time: 0:11
 */

namespace App\Modules\Invite;

use App\Modules\User\User;
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

    /**
     * 获取邀请的统计，通过日期分组
     * @param $userId
     * @param $date
     * @return array
     */
    public static function getInviteStatisticsByDate($userId, $date)
    {
        $firstDay = date('Y-m-01 00:00:00', strtotime($date));
        $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay + 1 month - 1 day"));
        $inviteUserRecords = InviteUserRecord::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->where('created_at', '<', $lastDay)
            ->limit(20)
            ->get();
        $dateList = [];
        foreach ($inviteUserRecords as $item) {
            $dateList[] = date('Y-m', strtotime($item->created_at));
            $dateList = array_unique($dateList);
        }

        $data = [];
        foreach ($dateList as $value) {
            foreach ($inviteUserRecords as $inviteUserRecord) {
                if (date('Y-m', strtotime($inviteUserRecord->created_at)) == $value) {
                    $inviteUserRecord->user_mobile = User::where('id', $inviteUserRecord->user_id)->value('mobile');

                    $firstDay = date('Y-m-01 00:00:00', strtotime($value));
                    $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay + 1 month - 1 day"));
                    $data[$value]['sub'][] = $inviteUserRecord->toArray();
                    $data[$value]['count'] = InviteUserRecord::where('origin_id', $userId)
                        ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
                        ->whereBetween('created_at', [$firstDay, $lastDay])
                        ->count();
                }
            }
        }
        return $data;
    }

    /**
     * 获取用户的邀请总数
     * @param $userId
     * @return mixed
     */
    public static function getInviteUserCountById($userId)
    {
        $totalCount = InviteUserStatisticsDaily::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->sum('invite_count');
        return $totalCount;
    }

    /**
     * 获取用户的当天邀请总数
     * @param $userId
     * @return int
     */
    public static function getTodayInviteCountById($userId)
    {
        $todayInviteCount = InviteUserRecord::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->whereBetween('created_at', [date('Y-m-d 00:00:00', time()), date('Y-m-d 23:59:59', time())])
            ->count();
        return $todayInviteCount;
    }


}