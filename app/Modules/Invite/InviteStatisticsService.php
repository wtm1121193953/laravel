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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * 邀请记录统计相关服务
 * Class InviteStatisticsService
 * @package App\Modules\Invite
 */
class InviteStatisticsService
{

    /**
     * 根据日期更新每日统计数据
     * @param $date
     */
    public static function updateDailyStatisticsByDate($date)
    {
        if($date instanceof Carbon){
            $date = $date->format('Y-m-d');
        }
        // 查询出每个origin对应当天的邀请数量并存储到每日统计表中
        $list = InviteUserRecord::whereDate('created_at', $date)
            ->groupBy('origin_id', 'origin_type')
            ->select('origin_id', 'origin_type')
            ->selectRaw('count(1) as total')
            ->get();
        $list->each(function($item) use ($date){
            // 统计时先查询, 如果存在, 则在原基础上修改
            $statDaily = InviteStatisticsService::getDailyStatisticsByOriginInfoAndDate($item->origin_id, $item->origin_type, $date);
            if(empty($statDaily)){
                $statDaily = new InviteUserStatisticsDaily();
                $statDaily->date = $date;
                $statDaily->origin_id = $item->origin_id;
                $statDaily->origin_type = $item->origin_type;
            }
            $statDaily->invite_count = $item->total;
            $statDaily->save();
        });
    }

    /**
     * 根据邀请人及日期信息获取已统计出的数据
     * @param $originId
     * @param $originType
     * @param $date
     * @return InviteUserStatisticsDaily
     */
    public static function getDailyStatisticsByOriginInfoAndDate($originId, $originType, $date)
    {
        if($date instanceof \Carbon\Carbon){
            $date = $date->format('Y-m-d');
        }
        $stat = InviteUserStatisticsDaily::where('date', $date)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        return $stat;
    }

    /**
     * 根据日期获取邀请人当日的邀请数
     * @param $date
     * @param $originId
     * @param $originType
     * @return int
     */
    public static function getInviteCountByDate($originId, $originType, $date)
    {
        if($date instanceof \Carbon\Carbon){
            $date = $date->format('Y-m-d');
        }
        return InviteUserRecord::whereDate('created_at', $date)
            ->where('origin_id', $originId)
            ->select('origin_type', $originType)
            ->count();
    }

    /**
     * 根据邀请人信息获取今天的邀请数量
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型
     * @return int
     */
    public static function getTodayInviteCountByOriginInfo($originId, $originType)
    {
        return self::getInviteCountByDate($originId, $originType, Carbon::now());
    }

    /**
     * 获取用户类型邀请人当天的邀请数量
     * @param $userId
     * @return int
     */
    public static function getTodayInviteCountByUserId($userId)
    {
        return self::getTodayInviteCountByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
    }

    /**
     * 获取用户类型邀请人当天的邀请数量
     * @param $merchantId
     * @return int
     */
    public static function getTodayInviteCountByMerchantId($merchantId)
    {
        return self::getTodayInviteCountByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT);
    }

    /**
     * 获取当日的邀请记录统计, 返回一个 InviteUserStatisticsDaily 对象, 不入库
     * @param $originId
     * @param $originType
     * @return InviteUserStatisticsDaily
     */
    public static function getTodayStatisticsByOriginInfo($originId, $originType)
    {
        $today = new InviteUserStatisticsDaily();
        $date = date('Y-m-d');
        $today->date = $date;
        $today->invite_count = self::getTodayInviteCountByOriginInfo($originId, $originType);

        return $today;
    }

    /**
     * 获取每日统计记录, 不包含当日数据
     * @param $originId
     * @param $originType
     * @param array $params 查询条件
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getDailyStatisticsListByOriginInfo($originId, $originType, $params = [], $pageSize = 15)
    {
        $data = InviteUserStatisticsDaily::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->orderByDesc('date')
            ->paginate($pageSize);
        return $data;
    }

    /**
     * 获取商户的邀请总数
     * @param $originId
     * @param $originType
     * @return int
     */
    public static function getTotalInviteCountByOriginInfo($originId, $originType)
    {
        $totalCount = InviteUserStatisticsDaily::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->sum('invite_count');
        $todayInviteCount = self::getTodayInviteCountByOriginInfo($originId, $originType);
        return $totalCount + $todayInviteCount;
    }

    /**
     * 获取用户类型邀请人的邀请总数
     * @param $userId
     * @return int
     */
    public static function getTotalInviteCountByUserId($userId)
    {
        return self::getTotalInviteCountByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
    }

    /**
     * 获取商户类型邀请人的邀请总数
     * @param $merchantId
     * @return int
     */
    public static function getTotalInviteCountByMerchantId($merchantId)
    {
        return self::getTotalInviteCountByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT);
    }

    /**
     * 获取邀请的统计，通过日期分组 (用于用户端获取)
     * @param $userId
     * @param $date
     * @param int $page
     * @return array
     */
    public static function getInviteStatisticsListByDateForUser($userId, $date, $page = 1)
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
                    ->offset(10 * ($page - 1))
                    ->limit(10)
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
     * 根据渠道信息获取渠道邀请的用户列表
     * @param $originId
     * @param $originType
     * @param $params
     * @param bool $withQuery
     * @return User|array
     */
    public static function getInviteUsersByOriginInfo($originId, $originType, $params = [], $withQuery = false)
    {
        $mobile = array_get($params, 'mobile');
        $userIds = InviteUserRecord::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->select('user_id')
            ->get()
            ->pluck('user_id');
        $query = User::whereIn('id', $userIds)
            ->when($mobile, function (Builder $query) use ($mobile) {
                $query->where('mobile', 'like', "%$mobile%");
            })
            ->orderBy('created_at', 'desc');

        if($withQuery) return $query;

        $page = array_get($params, 'page', 1);
        $pageSize = array_get($params, 'pageSize', 15);
        $orderColumn = array_get($params, 'orderColumn', 'id');
        $orderType = array_get($params, 'orderType', 'descending');

        $total = $query->count();
        $data = $query->get();
        // todo 用户表添加 order_count 字段, 记录用户的下单数
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

    /**
     * 查询商户邀请用户的列表
     * @param $merchantId
     * @param string $mobile
     * @param bool $withQuery
     * @param array $param
     * @return User|array
     * @deprecated
     */
    public static function getInviteUsersByMerchantId($merchantId, $mobile = '', $withQuery = false, $param = [])
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


}