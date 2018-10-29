<?php

namespace App\Modules\User;


use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;

class UserStatisticsService extends BaseService
{
    /**
     * 用户营销统计
     * @param string $endTime
     */
    public static function statistics($endTime='')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $startTime = date('Y-m-d', strtotime($endTime));

        $users = User::all()->toArray();
        foreach ($users as $user) {
            $where['user_id'] = $user['id'];
            $where['date'] = substr($startTime, 0, 10);

            $row = [];
            $row = array_merge($row, $where);

            //邀请用户数量
            $row['invite_user_num'] = InviteUserRecord::where('origin_id', '=', $row['user_id'])
                ->where('origin_type', '=', InviteUserRecord::ORIGIN_TYPE_USER)
                ->where('created_at', '>=', $startTime)
                ->where('created_at', '<=', $endTime)
                ->count();

            // 总订单量（已完成）
            $row['order_finished_num'] = Order::where('user_id', '=', $row['user_id'])
                ->where('pay_time', '>=', $startTime)
                ->where('pay_time', '<=', $endTime)
                ->whereIn('status', Order::STATUS_FINISHED)
                ->count();

            //总订单金额（已完成）
            $row['order_finished_amount'] = Order::where('user_id', '=', $row['user_id'])
                ->where('pay_time', '>=', $startTime)
                ->where('pay_time', '<=', $endTime)
                ->whereIn('status', Order::STATUS_FINISHED)
                ->sum('pay_price');

            if ($row['invite_user_num'] != 0 && $row['order_finished_num'] != 0 && $row['order_finished_amount'] != 0) {
                (new UserStatistics())->updateOrCreate($where, $row);
            }
        }
    }
}