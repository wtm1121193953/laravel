<?php

namespace App\Support\Reapal;

use App\Modules\Log\LogDbService;
use App\Modules\Log\LogOrderNotifyReapal;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Support\Utils;

/**
 * 融宝支付
 * Class ReapalPay
 * @package App\Support
 */
class ReapalPay
{
    protected $merchantId;
    protected $sellerEmail;
    protected $merchantPrivateKey;
    protected $reapalPublicKey;
    protected $apiKey;
    protected $charset;

    protected $apiUrl;
    protected $apiVersion;
    protected $api_sign_type;

    public function __construct()
    {

        //正式商户ID
        $this->merchantId = '100000001304400';
        //正式商户邮箱
        $this->sellerEmail = 'evan.li@daqian520.com';
        $this->apiKey = '0f45d96ba3f4685f4f4abb7ca1133e686740e65be1d9983e7a4ga202ac6e852g';

        //商户私钥
        $this->merchantPrivateKey = resource_path('reapal/cert/user-rsa.pem');
        //融宝公钥
        $this->reapalPublicKey = resource_path('reapal/cert/itrus001.pem');

        $this->charset = 'utf-8';
        $this->apiUrl = 'http://api.reapal.com';
        $this->apiVersion = '4.0.1';
        $this->api_sign_type = 'MD5';
    }



    /**
     * 预支付接口, 返回调起微信支付需要的参数
     * @param array $param
     * @return mixed|string
     */
    public function prepay(array $param)
    {
        //参数数组
        $paramArr = [

            'merchant_id' => $this->merchantId,
            'order_no' => array_get($param, 'order_no'),
            'transtime' => date('Y-m-d H:i:s'),
            'currency' => '156',
            'total_fee' => array_get($param, 'total_fee') * 100,
            'title' => array_get($param, 'title'),
            'body' => array_get($param, 'body'),

            'client_type' => '0',
            'user_id' => array_get($param, 'open_id'),
            'appid_source' => config('platform.miniprogram.app_id'),
            'store_phone' => array_get($param, 'store_phone'),
            'store_name' => array_get($param, 'store_name'),
            'store_id' => array_get($param, 'merchantId'),
            'token_id' => Utils::create_uuid(),
            'terminal_type' => 'mobile',
            'terminal_info' => 'null_MAC/' . Utils::create_uuid() . '_SIM',
            'member_ip' => request()->ip(),
            'seller_email' => $this->sellerEmail,
            'notify_url' => url('/api/pay/reapalPayNotify'),
            'version' => $this->apiVersion,

        ];

        $url = $this->apiUrl . '/qrcode/scan/encryptline';
        $result = $this->apiPost($url, $paramArr);
        LogDbService::reapalPayRequest(1,$paramArr['order_no'],$paramArr,$result);
        return $result;
    }

    protected function apiPost($url, $data){
        $reapalMap = new ReapalUtils();
        $result = $reapalMap->send($data, $url, $this->apiKey, $this->reapalPublicKey, $this->merchantId, $this->apiVersion, $this->api_sign_type);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->decryptKey($response['encryptkey'], $this->merchantPrivateKey);
        $result = $reapalMap->decrypt($response['data'], $encryptkey);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 支付结果查询接口
     */
    public function paySearch($order_no)
    {

        //参数数组
        $paramArr = array(
            'merchant_id' => $this->merchantId,
            'order_no' => $order_no,
        );
        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl . '/qrcode/scan/encryptSearch';

        $result = $this->apiPost($url, $paramArr);


        dd($result);
    }

    /**
     * 退款接口
     */
    public function refund($order, $orderPay)
    {
        //参数数组
        $paramArr = [
            'merchant_id' => $this->merchantId,
            'refund_order_no' => $orderPay->transaction_no,
            'order_no' => $order->order_no,
            'amount' => $orderPay->amount * 100,
            'notify_url' => url('/api/pay/reapalRefundNotify'),
            'version' => $this->apiVersion,
        ];


        $url = $this->apiUrl . '/qrcode/scan/encryptRefundApply';
        $result = $this->apiPost($url, $paramArr);
        LogDbService::reapalPayRequest(1,$paramArr['order_no'],$paramArr,$result);
        dd($result);

    }

    /**
     * 退款接口查询
     */
    public function refundSearch()
    {
        //参数数组
        $paramArr = [
            'merchant_id' => request('merchant_id'),
            'refund_order_no' => request('refund_order_no'),
            'order_no' => request('order_no'),
        ];

        $merchantId = request('merchat_id') ? request('merchat_id') : $this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl . '/qrcode/scan/encryptRefundQuery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId, $this->apiVersion, $this->api_sign_type);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'], $this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey, $response['data']);

    }

    /**
     * 异步通知接口
     */

    //public function payNotify(){

    //获取参数
    /*$resultArr = json_decode(request(),true);
    return $resultArr;*/

    //$str = request()->getContent();
    //$xml = simplexml_load_string($str);
    //return $xml;
    //}

    /**
     * 异步通知接口(退款)
     */

    public function refundNotify()
    {

        //获取参数
        $resultArr = json_decode(request(), true);
        return $resultArr;
    }

    /**
     * 支付回调
     */
    public function payNotify()
    {
        $reapal = request()->getContent();
        //$reapal = 'data=%7B%22notify_id%22%3A%223bf4cce100a94544ab65bcbd80fa5613%22%2C%22open_id%22%3A%22oA7-Z5blKW1JGt2Cf7c8LRvmpe9s%22%2C%22order_no%22%3A%22O20180830203036729649%22%2C%22order_time%22%3A%222018-08-30+20%3A30%3A37%22%2C%22sign%22%3A%22ff61f3abc45c9b3a7533a20b59292d79%22%2C%22status%22%3A%22TRADE_FINISHED%22%2C%22store_name%22%3A%22%E7%A8%8B%E7%A8%8B%E5%AE%B6%22%2C%22store_phone%22%3A%2215989438364%22%2C%22total_fee%22%3A%221%22%2C%22trade_no%22%3A%2210180830003914450%22%7D&merchant_id=100000001304038&encryptkey=';
        if (empty($reapal)) {
            return '';
        }
        LogDbService::reapalNotify(LogOrderNotifyReapal::TYPE_PAY,$reapal);

        parse_str($reapal,$url_params_arr);
        $data = json_decode($url_params_arr['data'],true);

        $reapalMap = new ReapalUtils();
        $sign = $reapalMap->createSign($data, $this->apiKey);

        if ($data['sign'] == $sign) {
            if (!empty($data['trade_no']) && $data['status'] == 'TRADE_FINISHED') {
                OrderService::paySuccess($data['order_no'], $data['trade_no'], $data['total_fee'] / 100,Order::PAY_TYPE_REAPAL);
                echo 'success';
            } else {
                echo 'fail';
            }
        } else {
            echo 'sign error';
        }


    }


}