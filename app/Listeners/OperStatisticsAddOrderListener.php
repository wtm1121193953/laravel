<?php

namespace App\Listeners;

use App\Events\OrdersUpdatedEvent;
use App\Modules\Order\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Oper\OperStatistics;

/**
 * 处理订单状态改变对应的运营中心统计数据增量
 * Class OperStatisticsAddOrderListener
 * Author:   JerryChan
 * Date:     2018/9/20 16:33
 * @package App\Listeners
 */
class OperStatisticsAddOrderListener
{
    /**
     * Create the event listener.
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * @param  OrdersUpdatedEvent $event
     * @return void
     */
    public function handle( OrdersUpdatedEvent $event )
    {
        if (!$event->order->oper_id || (($event->order->status != Order::STATUS_PAID) && ($event->order->status == Order::STATUS_REFUNDED))) {
            // oper_id 异常 或者 订单状态不为付款或者退款完成 ，全部不往下执行
            return;
        }
        $operStatistics = OperStatistics::where('oper_id', $event->order->oper_id)
            ->whereDate($event->date)
            ->first();
        // 判断订单是付款or退款
        if ($event->order->status == Order::STATUS_PAID) {
            // 如果为付款了
            $operStatistics->order_paid_amount += $event->order->pay_price;     // 添加付款金额
            $operStatistics->order_paid_num++;                                  // 添加付款笔数
        }
        if ($event->order->status == Order::STATUS_REFUNDED) {
            // 如果为退款
            $operStatistics->order_refund_amount += $event->order->pay_price;     // 添加退款款金额
            $operStatistics->order_refund_num++;                                  // 添加退款款笔数
        }
        $operStatistics->save();
    }
}
