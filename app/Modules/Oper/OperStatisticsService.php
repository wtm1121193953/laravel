<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/20/020
 * Time: 15:43
 */

namespace App\Modules\Oper;

use App\BaseService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use Illuminate\Support\Facades\DB;

class OperStatisticsService extends BaseService
{
    public static function getList(array $params = [],bool $return_query = false)
    {
        $query = OperStatistics::select('oper_id',DB::raw('sum(merchant_num) as merchant_num,sum(user_num) as user_num,sum(order_paid_num) as order_paid_num,sum(order_refund_num) as order_refund_num,sum(order_paid_amount) as order_paid_amount,sum(order_refund_amount) as order_refund_amount '));



        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['oper_id'])) {
            $query->where('oper_id', '=', $params['oper_id']);
        }


        $query->groupBy('oper_id');
        $query->orderBy('oper_id', 'desc');
        $query->with('oper:id,name');

        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

        $data->each(function ($item) use ($params){
            $item->date = "{$params['startDate']}至{$params['endDate']}";
        });
        return $data;
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
//        $startTime = substr($endTime,0,10) . ' 00:00:00';
        $startTime = date('Y-m-d', strtotime($endTime));

        $opers = OperService::allNormalOpers();
        foreach ($opers as $o) {
            $where['oper_id'] = $o['id'];
            $where['date'] = substr($startTime,0,10);

            $row = [];
            $row = array_merge($row,$where);

            //商户数量
            $row['merchant_num'] = Merchant::where('oper_id','=',$row['oper_id'])
                ->whereBetween('active_time', [$startTime, $endTime])
                ->where('audit_status','=',1)
                ->count();

            //邀请用户数量
            $row['user_num'] = InviteUserRecord::where('origin_id','=',$row['oper_id'])
                ->where('origin_type','=',3)
                ->whereBetween('created_at', [$startTime, $endTime])
                ->count();

            // 总订单量（已支付）
            $row['order_paid_num'] = Order::where('oper_id','=',$row['oper_id'])
                ->whereBetween('pay_time', [$startTime, $endTime])
                ->whereIn('status',[Order::STATUS_PAID,Order::STATUS_FINISHED])
                ->count();

            //总退款量
            $row['order_refund_num'] = Order::where('oper_id','=',$row['oper_id'])
                ->whereBetween('refund_time', [$startTime, $endTime])
                ->where('status','=',Order::STATUS_REFUNDED)
                ->count();

            //总订单金额（已支付）
            $row['order_paid_amount'] = Order::where('oper_id','=',$row['oper_id'])
                ->whereBetween('pay_time', [$startTime, $endTime])
                ->whereIn('status',[Order::STATUS_PAID,Order::STATUS_FINISHED])
                ->sum('pay_price');

            //总退款金额
            $row['order_refund_amount'] = Order::where('oper_id','=',$row['oper_id'])
                ->whereBetween('refund_time', [$startTime, $endTime])
                ->where('status','=',Order::STATUS_REFUNDED)
                ->sum('pay_price');

            OperStatistics::updateOrCreate($where,$row);
        }
    }
}