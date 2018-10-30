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
                ->where('status', Order::STATUS_FINISHED)
                ->count();

            //总订单金额（已完成）
            $row['order_finished_amount'] = Order::where('user_id', '=', $row['user_id'])
                ->where('pay_time', '>=', $startTime)
                ->where('pay_time', '<=', $endTime)
                ->where('status', Order::STATUS_FINISHED)
                ->sum('pay_price');

            if ($row['invite_user_num'] != 0 && $row['order_finished_num'] != 0 && $row['order_finished_amount'] != 0) {
                (new UserStatistics())->updateOrCreate($where, $row);
            }
        }
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

        $query->each(function ($item) use ($params){
            $item->date = "{$params['startDate']}至{$params['endDate']}";
        });

        $total = $query->count();
        $data = $query->get();

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