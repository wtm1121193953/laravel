<?php

namespace App\Modules\Merchant;


use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MerchantStatisticsService extends BaseService
{
    /**
     * 商户营销统计
     * @param string $endTime
     */
    public static function statistics($endTime = '')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d 23:59:59', strtotime('-1 day'));
        }

        $startTime = date('Y-m-d 00:00:00', strtotime($endTime));
        $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
        $date = date('Y-m-d', strtotime($endTime));

        if ($date >= date('Y-m-d', time())) return;

        $merchantArray = [];

        Order::where('status', Order::STATUS_FINISHED)
            ->whereBetween('pay_time', [$startTime, $endTime])
            ->chunk(1000, function($orders) use (&$merchantArray) {
                foreach ($orders as $order) {
                    if (!empty($merchantArray[$order->merchant_id])) {
                        $merchantArray[$order->merchant_id]['order_finished_amount'] += $order->pay_price;
                        $merchantArray[$order->merchant_id]['order_finished_num'] += 1;
                    } else {
                        $merchantArray[$order->merchant_id] = [
                            'order_finished_amount' => $order->pay_price,
                            'order_finished_num' => 1,
                            'invite_user_num' => 0,
                            'oper_id' => $order->oper_id,
                        ];
                    }
                }
            });

        InviteUserRecord::where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->chunk(1000, function ($inviteRecords) use (&$merchantArray) {
                 foreach ($inviteRecords as $inviteRecord) {
                     $merchant = MerchantService::getById($inviteRecord->origin_id);
                     if (empty($merchant)) {
                         Log::error('MerchantStatisticsService统计商户邀请人数, 商户为空', [
                             'inviteRecordId' => $inviteRecord->id,
                             'inviteRecord' => $inviteRecord,
                         ]);
                         continue;
                     }

                     if (!empty($merchantArray[$inviteRecord->origin_id])) {
                         $merchantArray[$inviteRecord->origin_id]['invite_user_num'] += 1;
                     } else {
                         $merchantArray[$inviteRecord->origin_id] = [
                             'order_finished_amount' => 0,
                             'order_finished_num' => 0,
                             'invite_user_num' => 1,
                             'oper_id' => $merchant->oper_id,
                         ];
                     }
                 }
            });

        if (!empty($merchantArray)) {
            foreach ($merchantArray as $key => $value) {
                $where['merchant_id'] = $key;
                $where['date'] = $date;

                $row['oper_id'] = $value['oper_id'];
                $row['order_finished_amount'] = $value['order_finished_amount'];
                $row['order_finished_num'] = $value['order_finished_num'];
                $row['invite_user_num'] = $value['invite_user_num'];

                $row = array_merge($row,$where);

                (new MerchantStatistics())->updateOrCreate($where, $row);
            }
        }
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

    /**
     * 查看是否有某个商户的统计记录， 没有则新建
     * @param $merchantId
     * @param $date
     * @param $operId
     * @return MerchantStatistics
     */
    private static function getStatisticsByMerchantIdAndDate($merchantId, $date, $operId)
    {
        $merchantStatistics = MerchantStatistics::where('date', $date)
            ->where('merchant_id', $merchantId)
            ->first();
        if (empty($merchantStatistics)) {
            $merchantStatistics = new MerchantStatistics();
            $merchantStatistics->date = $date;
            $merchantStatistics->merchant_id = $merchantId;
            $merchantStatistics->oper_id = $operId;
            $merchantStatistics->save();
        }
        return $merchantStatistics;
    }

    /**
     * 更新商户营销统计 的 邀请会员数量
     * @param $merchantId
     * @param $date
     * @return MerchantStatistics
     */
    public static function updateStatisticsInviteInfo($merchantId, $date)
    {
        $startTime = date('Y-m-d 00:00:00', strtotime($date));
        $endTime = date('Y-m-d 23:59:59', strtotime($date));
        $date = date('Y-m-d', strtotime($date));

        if ($date >= date('Y-m-d', time())) return;

        $inviteUserRecordCount = InviteUserRecord::where('origin_id', $merchantId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->whereBetween('created_at', [$startTime, $endTime])
            ->count();

        $merchant = MerchantService::getById($merchantId);
        if (empty($merchant)) {
            Log::error('MerchantStatisticsService更新商户邀请人数, 商户为空', [
                'merchantId' => $merchantId,
                'date' => $date,
            ]);
            return;
        }
        $merchantStatistics = self::getStatisticsByMerchantIdAndDate($merchantId, $date, $merchant->oper_id);
        $merchantStatistics->invite_user_num = $inviteUserRecordCount;
        $merchantStatistics->save();

        return $merchantStatistics;
    }
}