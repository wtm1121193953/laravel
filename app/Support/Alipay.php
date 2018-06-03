<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/3
 * Time: 15:45
 */

namespace App\Support;


require 'alipay-sdk/AopSdk.php';

use AlipayTradeAppPayRequest;
use AopClient;
use App\Modules\Order\Order;

class Alipay
{
    public static function pay(Order $order)
    {
        $aop = new AopClient;
        $aop->gatewayUrl = config('alipay.gateway_url');
        $aop->appId = config('alipay.app_id');
        $aop->rsaPrivateKey = config('alipay.rsa_private_key');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = config('alipayrsa_public_key');

        $request = new AlipayTradeAppPayRequest();

        $data = [
            'body' => $order->goods_name,
            'subject' => $order->merchant_name,
            'timeout_express' => '1d',
            'total_amount' => $order->pay_price,
            'out_trade_no' => $order->order_no,
        ];

        $bizcontent = json_encode($data);
        $request->setNotifyUrl(request()->getSchemeAndHttpHost()."/api/pay/alipayNotify");
        $request->setBizContent($bizcontent);

        $response = $aop->sdkExecute($request);  //这里和普通的接口调用不同，使用的是sdkExecute

        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
    }

    public static function verify($data)
    {
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = config('alipayrsa_public_key');
        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");
        return $flag;
    }
}