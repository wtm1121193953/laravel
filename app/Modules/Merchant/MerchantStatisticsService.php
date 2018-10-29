<?php

namespace App\Modules\Merchant;


use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;

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

        $merchants = MerchantService::getList([
            'auditStatus' => Merchant::AUDIT_STATUS_SUCCESS,
        ], true)
            ->get()
            ->toArray();
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
                ->whereIn('status', Order::STATUS_FINISHED)
                ->count();

            //总订单金额（已完成）
            $row['order_finished_amount'] = Order::where('merchant_id', '=', $row['merchant_id'])
                ->where('pay_time', '>=', $startTime)
                ->where('pay_time', '<=', $endTime)
                ->whereIn('status', Order::STATUS_FINISHED)
                ->sum('pay_price');

            (new MerchantStatistics)->updateOrCreate($where, $row);
        }
    }
}