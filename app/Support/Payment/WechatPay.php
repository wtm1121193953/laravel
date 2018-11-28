<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/18/018
 * Time: 15:25
 */
namespace App\Support\Payment;

use App\Exceptions\BaseResponseException;
use App\Modules\CsStatistics\CsStatisticsMerchantOrderService;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Log\LogDbService;
use App\Modules\Oper\OperMiniprogramService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;
use App\Modules\Order\OrderService;
use App\Modules\Platform\PlatformTradeRecord;
use App\Modules\Wechat\WechatService;
use App\Result;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WechatPay extends PayBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string|\Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function doNotify()
    {
        $str = request()->getContent();
        LogDbService::wechatNotify($str);
        $xml = simplexml_load_string($str);
        if (empty($str)) {
            return '';
        }
        // 获取aphid
        foreach ($xml->children() as $child) {
            if(strtolower($child->getName()) == 'appid'){
                $appid = $child . '';
            }
        }
        // 获取appid对应的运营中心小程序
        $config_platfrom = config('platform');

        if ($appid == $config_platfrom['miniprogram']['app_id']) {
            $app = WechatService::getWechatPayAppForPlatform();
        } elseif($appid == $config_platfrom['wechat_open']['app_id']) {
            $app = WechatService::getOpenPlatformPayAppFromPlatform();
        }else{
            $miniprogram = OperMiniprogramService::getByAppid($appid);
            $app = WechatService::getWechatPayAppForOper($miniprogram->oper_id);
        }

        $response = $app->handlePaidNotify(function ($message, $fail){
            if($message['return_code'] === 'SUCCESS' && array_get($message, 'result_code') === 'SUCCESS'){
                $orderNo = $message['out_trade_no'];
                $totalFee = $message['total_fee'];
                $payTime = $message['time_end'];
                return OrderService::paySuccess($orderNo, $message['transaction_id'], $totalFee / 100, Order::PAY_TYPE_WECHAT, $payTime);
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });
        return $response;
    }

    /**
     * @param $order
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function refund($order)
    {
        if($order->status != Order::STATUS_PAID && $order->status != Order::STATUS_UNDELIVERED){
            throw new BaseResponseException('订单状态不允许退款');
        }
        if ($order->pay_type != Order::PAY_TYPE_WECHAT) {
            throw new BaseResponseException('不是微信支付的订单');
        }
        // 查询支付记录
        $orderPay = OrderPay::where('order_id', $order->id)->firstOrFail();
        // 生成退款单
        $orderRefund = new OrderRefund();
        $orderRefund->order_id = $order->id;
        $orderRefund->order_no = $order->order_no;
        $orderRefund->amount = $orderPay->amount;
        $orderRefund->save();


        if($order->origin_app_type == Order::ORIGIN_APP_TYPE_MINIPROGRAM){
            if($order->pay_target_type == Order::PAY_TARGET_TYPE_OPER){
                $payApp = WechatService::getWechatPayAppForOper($order->oper_id);
            }else {
                $payApp = WechatService::getWechatPayAppForPlatform();
            }
        }else{
            // 获取平台的微信支付实例
            $payApp = WechatService::getOpenPlatformPayAppFromPlatform();
        }
        $result = $payApp->refund->byTransactionId($orderPay->transaction_no, $orderRefund->id, $orderPay->amount * 100, $orderPay->amount * 100, [
            'refund_desc' => '用户发起退款',
        ]);
        if ($result['return_code'] === 'SUCCESS' && array_get($result, 'result_code') === 'SUCCESS') {
            // 微信退款成功
            $orderRefund->refund_id = $result['refund_id'];
            $orderRefund->status = 2;
            $orderRefund->save();

            $order->status = Order::STATUS_REFUNDED;
            $order->refund_time = Carbon::now();
            $order->refund_price = $orderPay->amount;
            $order->save();

            $platform_trade_record = new PlatformTradeRecord();
            $platform_trade_record->type = PlatformTradeRecord::TYPE_REFUND;
            $platform_trade_record->pay_id = 1;
            $platform_trade_record->trade_amount = $orderPay->amount;
            $platform_trade_record->trade_time = $order->refund_time;
            $platform_trade_record->trade_no = $orderRefund->refund_id;
            $platform_trade_record->order_no = $order->order_no;
            $platform_trade_record->oper_id = $order->oper_id;
            $platform_trade_record->merchant_id = $order->merchant_id;
            $platform_trade_record->user_id = $order->user_id;
            $platform_trade_record->remark = '';
            $platform_trade_record->save();
            //如果是超市商户，更新商户当月销量
            if ($order->type == Order::TYPE_SUPERMARKET) {
                CsStatisticsMerchantOrderService::minusCsMerchantOrderNumberToday($order->merchant_id);
            }
            return Result::success($orderRefund);
        } else {
            Log::error('微信退款失败 :', [
                'result' => $result,
                'params' => [
                    'orderPay' => $orderPay->toArray(),
                    'orderRefund' => $orderRefund->toArray(),
                ]
            ]);
            throw new BaseResponseException('微信退款失败');
        }

    }

}