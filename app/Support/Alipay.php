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
use AlipayTradeRefundRequest;
use AopClient;
use App\Modules\Order\Order;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderRefund;

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
            'product_code' => 'QUICK_MSECURITY_PAY',
        ];

        $bizcontent = json_encode($data, JSON_UNESCAPED_UNICODE);
        $request->setNotifyUrl(request()->getSchemeAndHttpHost()."/api/pay/alipayNotify");
        $request->setBizContent($bizcontent);

        $response = $aop->sdkExecute($request);  //这里和普通的接口调用不同，使用的是sdkExecute

        return $response;//就是orderString 可以直接给客户端请求，无需再做处理。
    }

    public static function verify($data)
    {
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = config('alipayrsa_public_key');
        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");
        return $flag;
    }

    public static function refund(OrderPay $orderPay, OrderRefund $orderRefund)
    {
        $aop = new AopClient ();
        $aop->gatewayUrl = config('alipay.gateway_url');
        $aop->appId = config('alipay.app_id');
        $aop->rsaPrivateKey = config('alipay.rsa_private_key');
        $aop->alipayrsaPublicKey= config('alipayrsa_public_key');
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $request = new AlipayTradeRefundRequest();

        $data = [
            'refund_amount' => $orderPay->amount,
            'out_request_no' => $orderRefund->id,
            'trade_no' => $orderPay->transaction_no,
            'out_trade_no' => $orderPay->order_no,
        ];

        $bizcontent = json_encode($data);
        $request->setBizContent($bizcontent);
        $response = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $result = $response->$responseNode;

        return $result;
    }
}