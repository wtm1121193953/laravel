<?php

namespace App\Modules\Oper;

use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantStatistics;
use App\Modules\Order\Order;
use Illuminate\Support\Facades\DB;

class OperStatisticsService extends BaseService
{
    /**
     * 获取运营中心营销统计数据
     * @param array $params
     * @param bool $return_query
     * @return OperStatistics|array
     */
    public static function getList(array $params = [], bool $return_query = false)
    {
        $query = OperStatistics::select('oper_id',DB::raw('sum(merchant_num) as merchant_num,sum(merchant_pilot_num) as merchant_pilot_num, sum(merchant_total_num) as merchant_total_num, sum(user_num) as user_num, sum(merchant_invite_num) as merchant_invite_num, sum(oper_and_merchant_invite_num) as oper_and_merchant_invite_num, sum(order_paid_num) as order_paid_num,sum(order_refund_num) as order_refund_num,sum(order_paid_amount) as order_paid_amount,sum(order_refund_amount) as order_refund_amount '));

        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['operId'])) {
            $query->where('oper_id', '=', $params['operId']);
        }


        $query->groupBy('oper_id');
        $query->orderBy('oper_id', 'desc');
        $query->with('oper:id,name,province,city');

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
     * 运营中心单日运营数据统计
     * @param string $endTime
     */
    public static function statistics($endTime='')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $startTime = date('Y-m-d', strtotime($endTime));

        OperService::allNormalOpers(true)->chunk(1000, function ($opers) use ($startTime, $endTime) {
            foreach ($opers as $o) {
                $where['oper_id'] = $o['id'];
                $where['date'] = substr($startTime,0,10);

                $row = [];
                $row = array_merge($row,$where);

                //商户数量（正式）
                $row['merchant_num'] = Merchant::where('oper_id','=',$row['oper_id'])
                    ->where('first_active_time','>=',$startTime)
                    ->where('first_active_time','<=',$endTime)
                    ->where('is_pilot', Merchant::NORMAL_MERCHANT)
                    ->where('audit_status','=',Merchant::AUDIT_STATUS_SUCCESS)
                    ->count();

                //试点商户数量
                $row['merchant_pilot_num'] = Merchant::where('oper_id','=',$row['oper_id'])
                    ->where('first_active_time','>=',$startTime)
                    ->where('first_active_time','<=',$endTime)
                    ->where('is_pilot', Merchant::PILOT_MERCHANT)
                    ->where('audit_status','=',Merchant::AUDIT_STATUS_SUCCESS)
                    ->count();

                //商户总数量
                $row['merchant_total_num'] = Merchant::where('oper_id','=',$row['oper_id'])
                    ->where('first_active_time','>=',$startTime)
                    ->where('first_active_time','<=',$endTime)
                    ->where('audit_status','=',Merchant::AUDIT_STATUS_SUCCESS)
                    ->count();

                //邀请用户数量
                $row['user_num'] = InviteUserRecord::where('origin_id','=',$row['oper_id'])
                    ->where('origin_type','=',InviteUserRecord::ORIGIN_TYPE_OPER)
                    ->where('created_at','>=',$startTime)
                    ->where('created_at','<=',$endTime)
                    ->count();

                //运营中心的商户邀请的会员数量
                $row['merchant_invite_num'] = MerchantStatistics::where('oper_id', $row['oper_id'])
                    ->where('date', '>=', $startTime)
                    ->where('date', '<=', $endTime)
                    ->sum('invite_user_num');

                //运营中心及商户共邀请会员数
                $row['oper_and_merchant_invite_num'] = $row['user_num'] + $row['merchant_invite_num'];

                // 总订单量（已完成）
                $row['order_paid_num'] = Order::where('oper_id','=',$row['oper_id'])
                    ->where('pay_time','>=',$startTime)
                    ->where('pay_time','<=',$endTime)
                    ->where('status', Order::STATUS_FINISHED)
                    ->count();

                //总退款量
                $row['order_refund_num'] = Order::where('oper_id','=',$row['oper_id'])
                    ->where('refund_time','>=',$startTime)
                    ->where('refund_time','<=',$endTime)
                    ->where('status','=',Order::STATUS_REFUNDED)
                    ->count();

                //总订单金额（已完成）
                $row['order_paid_amount'] = Order::where('oper_id','=',$row['oper_id'])
                    ->where('pay_time','>=',$startTime)
                    ->where('pay_time','<=',$endTime)
                    ->where('status', Order::STATUS_FINISHED)
                    ->sum('pay_price');

                //总退款金额
                $row['order_refund_amount'] = Order::where('oper_id','=',$row['oper_id'])
                    ->where('refund_time','>=',$startTime)
                    ->where('refund_time','<=',$endTime)
                    ->where('status','=',Order::STATUS_REFUNDED)
                    ->sum('pay_price');

                if ($row['merchant_num'] != 0 && $row['merchant_pilot_num'] != 0 && $row['merchant_total_num'] != 0 && $row['user_num'] != 0 && $row['merchant_invite_num'] != 0 && $row['oper_and_merchant_invite_num'] != 0 && $row['order_paid_num'] != 0 && $row['order_refund_num'] != 0 && $row['order_paid_amount'] != 0 && $row['order_refund_amount'] != 0) {
                    (new OperStatistics)->updateOrCreate($where, $row);
                }
            }
        });
    }

    /**
     * 审核通过的试点商户 转 正式商户的时候，修改统计数据
     * @param $operId
     * @param $date
     */
    public static function updateMerchantNum($operId, $date)
    {
        $operStatistics = OperStatistics::where('oper_id', $operId)
            ->where('date', $date)
            ->first();
        if (!empty($operStatistics)) {
            $operStatistics->increment('merchant_num');
            $operStatistics->decrement('merchant_pilot_num');
        }
    }
}