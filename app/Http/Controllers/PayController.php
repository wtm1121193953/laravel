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
use App\Modules\Order\OrderPay;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{

    /**
     * 支付通知接口, 用于接收微信支付的通知
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function notify()
    {
        if(App::environment() === 'local' || request()->get('mock')){
            $this->paySuccess(request('order_no'), 'mock transaction id', 0);
            return Result::success('模拟支付成功');
        }
        $app = WechatService::getWechatPayAppForOper(1);
        $response = $app->handlePaidNotify(function ($message, $fail){
            if($message['return_code'] === 'SUCCESS' && array_get($message, 'result_code') === 'SUCCESS'){
                $orderNo = $message['out_trade_no'];
                $this->paySuccess($orderNo, $message['transaction_id'], $message['total_fee']);
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            // 其他未知情况
            return false;
        });
        return $response;
    }

    /**
     * 支付成功
     * @param $orderNo
     * @param $transactionId
     * @param $totalFee
     * @return bool
     */
    private function paySuccess($orderNo, $transactionId, $totalFee)
    {
        // 处理订单支付成功逻辑
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if($order->status === Order::STATUS_UN_PAY
            || $order->status === Order::STATUS_CANCEL
            || $order->status === Order::STATUS_CLOSED
        ){
            $order->pay_time = Carbon::now(); // 更新支付时间为当前时间
            $order->status = Order::STATUS_PAID;
            $order->save();

            // 生成核销码, 线上需要放到支付成功通知中
            for ($i = 0; $i < $order->buy_number; $i ++){
                $orderItem = new OrderItem();
                $orderItem->oper_id = $order->oper_id;
                $orderItem->merchant_id = $order->merchant_id;
                $orderItem->order_id = $order->id;
                $orderItem->verify_code = OrderItem::createVerifyCode($order->merchant_id);
                $orderItem->status = 1;
                $orderItem->save();
            }
            // 生成订单支付记录
            $orderPay = new OrderPay();
            $orderPay->order_id = $order->id;
            $orderPay->order_no = $orderNo;
            $orderPay->transaction_no = $transactionId;
            $orderPay->amount = $totalFee * 1.0 / 100;
            $orderPay->save();

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
    }
}