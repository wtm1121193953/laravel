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

class OperStatisticsService extends BaseService
{
    public static function getList(array $params = [],bool $return_query = false)
    {
        $query = OperStatistics::select('*');


        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }
        if (!empty($params['oper_id'])) {
            $query->where('oper_id', '=', $params['oper_id']);
        }

        $query->orderBy('id', 'desc');
        $query->with('oper:id,name');

        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

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

        $startTime = substr($endTime,0,10) . ' 00:00:00';


        $opers = OperService::allNormalOpers();

        foreach ($opers as $o) {
            $where['oper_id'] = $o['id'];
            $where['date'] = substr($startTime,0,10);

            $row = [];
            $row = array_merge($row,$where);

            //商户数量
            $row['merchant_num'] = Merchant::where('oper_id','=',$row['oper_id'])
                ->where('created_at','>=',$startTime)
                ->where('created_at','<=',$endTime)
                ->count();

            //邀请用户数量
            $row['user_num'] = InviteUserRecord::where('origin_id','=',$row['oper_id'])
                ->where('origin_type','=',3)
                ->where('created_at','>=',$startTime)
                ->where('created_at','<=',$endTime)
                ->count();

            // 总订单量（已支付）
            $row['order_paid_num'] = Order::where('oper_id','=',$row['oper_id'])
                ->where('pay_time','>=',$startTime)
                ->where('pay_time','<=',$endTime)
                ->whereIn('status',[Order::STATUS_PAID,Order::STATUS_FINISHED])
                ->count();

            //总退款量
            $row['order_refund_num'] = Order::where('oper_id','=',$row['oper_id'])
                ->where('refund_time','>=',$startTime)
                ->where('refund_time','<=',$endTime)
                ->where('status','=',Order::STATUS_REFUNDED)
                ->count();

            //总订单金额（已支付）
            $row['order_paid_amount'] = Order::where('oper_id','=',$row['oper_id'])
                ->where('pay_time','>=',$startTime)
                ->where('pay_time','<=',$endTime)
                ->whereIn('status',[Order::STATUS_PAID,Order::STATUS_FINISHED])
                ->sum('pay_price');

            //总退款金额
            $row['order_refund_amount'] = Order::where('oper_id','=',$row['oper_id'])
                ->where('refund_time','>=',$startTime)
                ->where('refund_time','<=',$endTime)
                ->where('status','=',Order::STATUS_REFUNDED)
                ->sum('pay_price');


            $exist = OperStatistics::select('*')
                ->where('oper_id',$where['oper_id'])
                ->where('date',$where['date'])
                ->first();


            if ($exist) {
                $exist->merchant_num = $row['merchant_num'];
                $exist->user_num = $row['user_num'];
                $exist->order_paid_num = $row['order_paid_num'];
                $exist->order_refund_num = $row['order_refund_num'];
                $exist->order_paid_amount = $row['order_paid_amount'];
                $exist->order_refund_amount = $row['order_refund_amount'];
                $rs = $exist->save();
            } else {
                $obj = new OperStatistics();
                $obj->oper_id = $row['oper_id'];
                $obj->date = $row['date'];
                $obj->merchant_num = $row['merchant_num'];
                $obj->user_num = $row['user_num'];
                $obj->order_paid_num = $row['order_paid_num'];
                $obj->order_refund_num = $row['order_refund_num'];
                $obj->order_paid_amount = $row['order_paid_amount'];
                $obj->order_refund_amount = $row['order_refund_amount'];
                $rs = $obj->save();
            }
        }


    }
}