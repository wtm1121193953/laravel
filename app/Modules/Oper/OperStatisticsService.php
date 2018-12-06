<?php

namespace App\Modules\Oper;

use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantStatistics;
use App\Modules\Order\Order;
use Illuminate\Database\Eloquent\Builder;
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
        $query->with('oper:id,name,province,city');

        $orderColumn = $params['orderColumn'] ?: 'oper_id';
        $orderType = $params['orderType'] ?: 'descending';
        $query->orderBy($orderColumn, $orderType == 'descending' ? 'desc' : 'asc');

        if ($return_query) {
            return  $query;
        }
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 15;

        $data = $query->paginate($pageSize);

        $data->each(function ($item) use ($params){
            $item->date = "{$params['startDate']}至{$params['endDate']}";
        });

        return [
            'data' => $data->items(),
            'total' => $data->total(),
        ];
    }


    /**
     * 运营中心单日运营数据统计
     * @param string $endTime
     */
    public static function statistics($endTime = '')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d 23:59:59', strtotime('-1 day'));
        }

        OperService::allNormalOpers(true)
            ->chunk(1000, function ($opers) use ($endTime) {
            foreach ($opers as $oper) {
                self::updateStatistics($oper->id, $endTime);
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

    /**
     * 更新运营中心的 营销统计
     * @param $operId
     * @param $date
     */
    public static function updateStatistics($operId, $date)
    {
        $startTime = date('Y-m-d 00:00:00', strtotime($date));
        $endTime = date('Y-m-d 23:59:59', strtotime($date));
        $date = date('Y-m-d', strtotime($date));

        if ($date >= date('Y-m-d', time())) return;

        $merchantNum = 0;
        $merchantPilotNum = 0;
        $merchantTotalNum = 0;
        Merchant::where('oper_id', $operId)
            ->whereBetween('first_active_time', [$startTime, $endTime])
            ->where('audit_status',Merchant::AUDIT_STATUS_SUCCESS)
            ->chunk(1000, function ($merchants) use (&$merchantNum, &$merchantPilotNum, &$merchantTotalNum) {
                foreach ($merchants as $merchant) {
                    if ($merchant->is_pilot == Merchant::NORMAL_MERCHANT) {
                        $merchantNum += 1;
                    } else {
                        $merchantPilotNum += 1;
                    }
                    $merchantTotalNum += 1;
                }
            });

        $userNum = InviteUserRecord::where('origin_id', $operId)
            ->where('origin_type',InviteUserRecord::ORIGIN_TYPE_OPER)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();
        $merchantInviteNum = MerchantStatistics::where('oper_id', $operId)
            ->where('date',  $date)
            ->sum('invite_user_num');
        $operAndMerchantInviteNum = $userNum + $merchantInviteNum;

        $orderPaidNum = 0;
        $orderRefundNum = 0;
        $orderPaidAmount = 0;
        $orderRefundAmount = 0;
        Order::where('oper_id',$operId)
            ->where(function(Builder $query) use ($startTime, $endTime){
                $query->where(function(Builder $query) use ($startTime, $endTime){
                    $query->where('status', Order::STATUS_FINISHED)
                        ->whereBetween('pay_time', [$startTime, $endTime]);
                })->orWhere(function(Builder $query) use ($startTime, $endTime){
                    $query->where('status', Order::STATUS_REFUNDED)
                        ->whereBetween('refund_time', [$startTime, $endTime]);
                });
            })
            ->chunk(1000, function ($orders) use (&$orderPaidNum, &$orderRefundNum, &$orderPaidAmount, &$orderRefundAmount, $startTime, $endTime) {
                foreach ($orders as $order) {
                    if ($order->status == Order::STATUS_FINISHED && $order->pay_time >= $startTime && $order->pay_time <= $endTime) {
                        $orderPaidNum += 1;
                        $orderPaidAmount += $order->pay_price;
                    }
                    if ($order->status == Order::STATUS_REFUNDED && $order->refund_time >= $startTime && $order->refund_time <= $endTime) {
                        $orderRefundNum += 1;
                        $orderRefundAmount += $order->refund_price;
                    }
                }
            });

        $where['oper_id'] = $operId;
        $where['date'] = $date;

        $row = [
            'merchant_num' => $merchantNum,
            'merchant_pilot_num' => $merchantPilotNum,
            'merchant_total_num' => $merchantTotalNum,
            'user_num' => $userNum,
            'merchant_invite_num' => $merchantInviteNum,
            'oper_and_merchant_invite_num' => $operAndMerchantInviteNum,
            'order_paid_num' => $orderPaidNum,
            'order_refund_num' => $orderRefundNum,
            'order_paid_amount' => $orderPaidAmount,
            'order_refund_amount' => $orderRefundAmount,
        ];
        $row = array_merge($row,$where);

        (new OperStatistics)->updateOrCreate($where, $row);
    }

    /**
     * 更新运营中心营销统计 的 邀请相关的数据
     * @param $operId
     * @param $date
     */
    public static function updateStatisticsInviteInfo($operId, $date)
    {
        $startTime = date('Y-m-d 00:00:00', strtotime($date));
        $endTime = date('Y-m-d 23:59:59', strtotime($date));
        $date = date('Y-m-d', strtotime($date));

        if ($date >= date('Y-m-d', time())) return;

        $userNum = InviteUserRecord::where('origin_id', $operId)
            ->where('origin_type',InviteUserRecord::ORIGIN_TYPE_OPER)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();
        $merchantInviteNum = MerchantStatistics::where('oper_id', $operId)
            ->where('date',  $date)
            ->sum('invite_user_num');
        $operAndMerchantInviteNum = $userNum + $merchantInviteNum;

        $where['oper_id'] = $operId;
        $where['date'] = $date;

        $row = [
            'user_num' => $userNum,
            'merchant_invite_num' => $merchantInviteNum,
            'oper_and_merchant_invite_num' => $operAndMerchantInviteNum,
        ];
        $row = array_merge($row,$where);

        (new OperStatistics)->updateOrCreate($where, $row);
    }
}