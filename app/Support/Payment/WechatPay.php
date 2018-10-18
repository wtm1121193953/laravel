<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/18/018
 * Time: 15:25
 */
namespace App\Support\Payment;

use App\Modules\Log\LogDbService;
use App\Modules\Oper\OperMiniprogramService;
use App\Modules\Order\OrderService;
use App\Modules\Wechat\WechatService;
use test\Mockery\Fixtures\EmptyTestCaseV5;

class WechatPay extends PayBase
{
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
                OrderService::paySuccess($orderNo, $message['transaction_id'], $totalFee / 100, Order::PAY_TYPE_WECHAT, $payTime);
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            // 其他未知情况
            return false;
        });
        return $response;
    }
}