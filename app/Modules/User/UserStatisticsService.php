<?php

namespace App\Modules\User;


use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;
use Illuminate\Support\Facades\DB;

class UserStatisticsService extends BaseService
{
    /**
     * 用户营销统计
     * @param string $endTime
     */
    public static function statistics($endTime = '')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $startTime = date('Y-m-d 00:00:00', strtotime($endTime));
        $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
        $date = date('Y-m-d', $endTime);

        Order::where('status', Order::STATUS_FINISHED)
            ->whereBetween('pay_time', [$startTime, $endTime])
            ->chunk(1000, function($orders) use ($date) {
                foreach ($orders as $order) {
                    $userStatistics = self::getStatisticsByUserIdAndDate($order->user_id, $date);

                    $userStatistics->increment('order_finished_amount', $order->pay_price);
                    $userStatistics->increment('order_finished_num', 1);
                }
            });

        InviteUserRecord::where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->chunk(1000, function ($inviteRecords) use ($date) {
                foreach ($inviteRecords as $inviteRecord) {
                    $userStatistics = self::getStatisticsByUserIdAndDate($inviteRecord->origin_id, $date);

                    $userStatistics->increment('invite_user_num', 1);
                }
            });
    }

    /**
     * 获取用户营销统计
     * @param array $params
     * @param bool $return_query
     * @return UserStatistics|array
     */
    public static function getList(array $params = [], bool $return_query = false)
    {
        $query = UserStatistics::select('user_id',DB::raw('sum(invite_user_num) as invite_user_num, sum(order_finished_amount) as order_finished_amount, sum(order_finished_num) as order_finished_num '));

        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['userId'])) {
            $query->where('user_id', '=', $params['userId']);
        }


        $query->groupBy('user_id');
        $query->orderBy('user_id', 'desc');
        $query->with('user:id,mobile');

        if ($return_query) {
            return  $query;
        }
        $page = $params['page'] ?: 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 15;
        $orderColumn = $params['orderColumn'];
        $orderType = $params['orderType'];

        $data = $query->get();
        $total = $query->get()->count();

        $data->each(function ($item) use ($params){
            $item->date = "{$params['startDate']}至{$params['endDate']}";
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
     * 查看是否存在营销的用户统计记录，没有则新建
     * @param $userId
     * @param $date
     * @return UserStatistics
     */
    private static function getStatisticsByUserIdAndDate($userId, $date)
    {
        $userStatistics = UserStatistics::where('date', $date)
            ->where('user_id', $userId)
            ->first();
        if (empty($userStatistics)) {
            $userStatistics = new UserStatistics();
            $userStatistics->date = $date;
            $userStatistics->user_id = $userId;
            $userStatistics->save();
        }
        return $userStatistics;
    }

    /**
     * 更新用户营销统计 的 邀请会员数量
     * @param $userId
     * @param $date
     * @return UserStatistics
     */
    public static function updateStatisticsInviteInfo($userId, $date)
    {
        $startTime = date('Y-m-d 00:00:00', strtotime($date));
        $endTime = date('Y-m-d 23:59:59', strtotime($date));
        $date = date('Y-m-d', $date);

        if ($date >= date('Y-m-d', time())) return;

        $inviteUserRecordCount = InviteUserRecord::where('origin_id', $userId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_USER)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();

        $userStatistics = self::getStatisticsByUserIdAndDate($userId, $date);
        $userStatistics->invite_user_num = $inviteUserRecordCount;
        $userStatistics->save();

        return $userStatistics;
    }
}