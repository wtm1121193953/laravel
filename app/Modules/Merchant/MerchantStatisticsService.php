<?php

namespace App\Modules\Merchant;


use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;
use Illuminate\Support\Facades\DB;

class MerchantStatisticsService extends BaseService
{
    /**
     * 商户营销统计
     * @param string $endTime
     */
    public static function statistics($endTime='')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $startTime = date('Y-m-d', strtotime($endTime));

        MerchantService::getList([
            'auditStatus' => Merchant::AUDIT_STATUS_SUCCESS,
        ], true)
            ->chunk(1000, function ($merchants) use ($startTime, $endTime) {
                foreach ($merchants as $merchant) {
                    $where['merchant_id'] = $merchant['id'];
                    $where['date'] = substr($startTime, 0, 10);

                    $row = [
                        'oper_id' => $merchant['oper_id'],
                    ];
                    $row = array_merge($row, $where);

                    //邀请用户数量
                    $row['invite_user_num'] = InviteUserRecord::where('origin_id', '=', $row['merchant_id'])
                        ->where('origin_type', '=', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
                        ->where('created_at', '>=', $startTime)
                        ->where('created_at', '<=', $endTime)
                        ->count();

                    // 总订单量（已完成）
                    $row['order_finished_num'] = Order::where('merchant_id', '=', $row['merchant_id'])
                        ->where('pay_time', '>=', $startTime)
                        ->where('pay_time', '<=', $endTime)
                        ->where('status', Order::STATUS_FINISHED)
                        ->count();

                    //总订单金额（已完成）
                    $row['order_finished_amount'] = Order::where('merchant_id', '=', $row['merchant_id'])
                        ->where('pay_time', '>=', $startTime)
                        ->where('pay_time', '<=', $endTime)
                        ->where('status', Order::STATUS_FINISHED)
                        ->sum('pay_price');

                    if ($row['invite_user_num'] != 0 || $row['order_finished_num'] != 0 || $row['order_finished_amount'] != 0) {
                        (new MerchantStatistics)->updateOrCreate($where, $row);
                    }
                }

            });
    }

    /**
     * 获取商户营销统计
     * @param array $params
     * @param bool $return_query
     * @return MerchantStatistics|array
     */
    public static function getList(array $params = [], bool $return_query = false)
    {
        $query = MerchantStatistics::select('merchant_id',DB::raw('sum(invite_user_num) as invite_user_num, sum(order_finished_amount) as order_finished_amount, sum(order_finished_num) as order_finished_num '));

        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['merchantId'])) {
            $query->where('merchant_id', '=', $params['merchantId']);
        }


        $query->groupBy('merchant_id');
        $query->orderBy('merchant_id', 'desc');
        $query->with('merchant:id,name,province,city');

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
}