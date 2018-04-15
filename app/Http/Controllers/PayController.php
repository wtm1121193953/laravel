<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/15
 * Time: 16:24
 */

namespace App\Http\Controllers;


use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Wechat\WechatService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{

    /**
     * 支付通知接口, 用于接收微信支付的通知
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function notify()
    {
        $app = WechatService::getWechatPayAppForOper(1);
        $response = $app->handlePaidNotify(function ($message, $fail){
            if($message['return_code'] === 'SUCCESS' && array_get($message, 'result_code') === 'SUCCESS'){
                // 处理订单支付成功逻辑
                $orderNo = $message['out_trade_no'];
                $order = Order::where('order_no', $orderNo)->firstOrFail();

                if($order->status === Order::STATUS_UN_PAY
                    || $order->status === Order::STATUS_CANCEL
                    || $order->status === Order::STATUS_CLOSED
                ){
                    $order->pay_time = Carbon::now(); // 更新支付时间为当前时间
                    $order->status = Order::STATUS_PAID;
                    $order->save();

                    // 生成核销码, 线上需要放到支付成功通知中
                    for ($i = 0; $i < $order->number; $i ++){
                        $orderItem = new OrderItem();
                        $orderItem->oper_id = $order->oper_id;
                        $orderItem->merchant_id = $order->merchant_id;
                        $orderItem->order_id = $order->id;
                        $orderItem->verify_code = OrderItem::createVerifyCode($order->merchant_id);
                        $orderItem->status = 1;
                        $orderItem->save();
                    }
                    return true;
                }else if($order->status == Order::STATUS_PAID){
                    // 已经支付成功了
                    return true;
                }else if($order->status == Order::STATUS_REFUNDING
                    || $order->status === Order::STATUS_REFUNDED
                    || $order->status === Order::STATUS_FINISHED
                ){
                    // 订单已退款或已完成
                    return true;
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            // 其他未知情况
            return false;
        });
        return $response;
    }
}