<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/13
 * Time: 0:11
 */

namespace App\Modules\Invite;

use App\Modules\Order\Order;
use App\Modules\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use function PHPSTORM_META\type;

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
     * @param int $page
     * @return array
     */
    public static function getInviteStatisticsByDate($userId, $date, $page = 1)
    {
        $now = date('Y-m', time());
        $time = $date ?: $now;
        $firstDay = date('Y-m-01 00:00:00', strtotime($time));
        $lastDay = date('Y-m-d 23:59:59', strtotime("$firstDay + 1 month - 1 day"));
        if (!$date){
            $inviteUserRecords = InviteUserRecord::where('origin_id', $userId)
                ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
                ->where('created_at', '<', $lastDay)
                ->orderBy('created_at', 'desc')
                ->offset(20 * ($page - 1))
                ->limit(20)
                ->get();

        }else {
            if ($date > $now) {
                $inviteUserRecords = [];
            } else {
                $inviteUserRecords = InviteUserRecord::where('origin_id', $userId)
                    ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
                    ->whereBetween('created_at', [$firstDay, $lastDay])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

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
        $totalCount = InviteUserRecord::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->count();
        return $totalCount;
    }

    /**
     * 获取商户的邀请总数
     * @param $merchantId
     * @param $todayInviteCount
     * @return int
     */
    public static function getInviteUserCountByMerchantId($merchantId, $todayInviteCount)
    {
        $totalCount = InviteUserStatisticsDaily::where('origin_id', $merchantId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->sum('invite_count');
        return $totalCount + $todayInviteCount;
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

    /**
     * 查询商户邀请用户的列表
     * @param $merchantId
     * @param string $mobile
     * @param bool $withQuery
     * @param array $param
     * @return User|array
     */
    public static function getInviteRecordListByMerchantId($merchantId, $mobile = '', $withQuery = false, $param = [])
    {
        $userIds = InviteUserRecord::where('origin_id', $merchantId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->select('user_id')
            ->get()
            ->pluck('user_id');
        $query = User::whereIn('id', $userIds)
            ->when($mobile, function (Builder $query) use ($mobile) {
                $query->where('mobile', 'like', "%$mobile%");
            })
            ->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $page = $param['page'] ?: 1;
            $pageSize = $param['pageSize'] ?: 15;
            $orderColumn = $param['orderColumn'];
            $orderType = $param['orderType'];

            $total = $query->count();
            $data = $query->get();
            $data->each(function ($item) {
                $item->order_number = Order::where('user_id', $item->id)
                    ->whereNotIn('status', [Order::STATUS_UN_PAY, Order::STATUS_CLOSED])
                    ->count();
            });

            if ($orderType == 'descending') {
                $data = $data->sortBy($orderColumn);
            } elseif ($orderType == 'ascending') {
                $data = $data->sortByDesc($orderColumn);
            }

            $data = $data->forPage($page,$pageSize)->values()->all();

            return [
                'data' => $data,
                'total' => $total,
            ];
        }
    }

    public static function getDailyList($operId,$page)
    {
        $data = InviteUserStatisticsDaily::where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->orderByDesc('date')
            ->paginate();
        // 如果是第一页, 获取当日数据统计并添加到列表中
        if($page <= 1){
            $today = new InviteUserStatisticsDaily();
            $date = date('Y-m-d');
            $today->date = $date;
            $today->invite_count = InviteStatisticsService::getInviteCountByDate(
                $date, $operId, InviteChannel::ORIGIN_TYPE_OPER
            );
            if($today->invite_count > 0){
                $data->prepend($today);
            }
        }
        return $data;
    }


}