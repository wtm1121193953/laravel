<?php

namespace App\Support;

use Illuminate\Support\Carbon;

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
    protected $dsfUrl;
    protected $charset;
    protected $dsf_sign_type;
    protected $dsfVersion;

    protected $apiUrl;
    protected $apiVersion;
    protected $api_sign_type;

    public function __construct()
    {

        //测试商户ID
        $this->merchantId = config('reapal.merchant_id');
        //测试商户邮箱
        $this->sellerEmail = config('reapal.seller_email');

        //商户私钥
        $this->merchantPrivateKey = config('reapal.merchantPrivateKey');
        //融宝公钥
        $this->reapalPublicKey = config('reapal.reapalPublicKey');

        $this->apiKey   = config('reapal.apiKey');
        $this->dsfUrl   = config('reapal.dsfUrl');
        $this->charset   = config('reapal.charset');
        $this->dsf_sign_type   = config('reapal.dsf_sign_type');
        $this->dsfVersion   = config('reapal.dsf_version');

        $this->apiUrl   = config('reapal.api_url');
        $this->apiVersion   = config('reapal.api_version');
        $this->api_sign_type   = config('reapal.api_sign_type');
    }

    /**
     * 代付提交
     */
    public function agentpay($batch_no,$batch_count,$batch_amount,$content)
    {
        $reapalMap = new ReapalUtils();

        $nowTime = Carbon::now();

        $merchantId = $this->merchantId;

        $paramArr = array(
            'charset' => $this->charset,
            'notify_url' => url('/pay/reapalPayNotify'),
            'trans_time' => $nowTime,
            'batch_no' => $batch_no,
            'batch_count' => $batch_count,
            'batch_amount' => $batch_amount,
            'pay_type' => 1,
            'content' => $content,
        );

        $url = $this->dsfUrl.'agentpay/pay';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->dsfVersion,$this->dsf_sign_type);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);

    }

    /**
     * 代付批次查询
     */
    public function agentpayQueryBatch()
    {

        //参数数组
        $paramArr = array(
            'charset' => $this->charset,
            'notify_url' => $this->notify_url,
            'trans_time' => request('trans_time'),
            'batch_no' => request('batch_no'),
            'next_tag' => request('next_tag'),
        );

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl.'agentpay/batchpayquery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->dsfVersion);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);

    }

    /**
     * 代付单笔查询
     */
    public function agentpayQuerySingle()
    {
        //参数数组
        $paramArr = array(
            'charset' => $this->charset,
            'notify_url' => $this->notify_url,
            'trans_time' => request('trans_time'),
            'batch_no' => request('batch_no'),
            'detail_no' => request('detail_no'),
        );

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl.'agentpay/singlepayquery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->dsfVersion);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);

        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);
    }

    /**
     * 代付账户查询余额
     */
    public function agentpayQueryBalance()
    {

        //参数数组
        $paramArr = array(
            'charset' => $this->charset,
        );

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl.'agentpay/balancequery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->dsfVersion);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);
    }

    /**
     * 预支付接口, 返回调起微信支付需要的参数
     */
    public function prepay(array $param)
    {
        //参数数组
        $paramArr = [

            'seller_email'=> $this->sellerEmail,
            'merchant_id' => $this->merchantId,
            'body' => array_get($param,'body'),
            'title' => array_get($param,'title'),
            'order_no' => array_get($param,'order_no'),
            'total_fee' => array_get($param,'total_fee'),

            'notify_url' => url('/pay/payNotify'),
            'transtime' => Carbon::now(),
            'currency' => '156',
            'member_ip' => request()->ip(),
            'terminal_type' => 'mobile',
            'terminal_info' => 'null_MAC/'.Utils::create_uuid().'_SIM',
            'token_id' => Utils::create_uuid(),

            'client_type'=>'0',
            'user_id'=>request()->get('current_open_id'),
            'appid_source'=>config('platform.miniprogram.app_id'),
            'store_phone'=>array_get($param,'store_phone'),
            'store_name'=>array_get($param,'store_name'),
            'version'=>$this->apiVersion,

        ];

        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl.'/qrcode/scan/encryptline';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $this->merchantId);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);
    }

    /**
     * 支付结果查询接口
     */
    public function paySearch()
    {

        //参数数组
        $paramArr = array(
            'merchant_id' => $this->merchantId,
            'order_no' => request('order_no'),
        );
        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl.'/qrcode/scan/encryptSearch';

        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $this->merchantId);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);

        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);
    }

    /**
     * 退款接口
     */
    public function refund()
    {
        //参数数组
        $paramArr = [
            'merchant_id' => request('merchant_id'),
            'refund_order_no' => request('refund_order_no'),
            'order_no' => request('order_no'),
            'amount' => request('amount'),

        ];

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl.'/qrcode/scan/encryptRefundApply';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);

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

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->apiUrl.'/qrcode/scan/encryptRefundQuery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->apiVersion,$this->api_sign_type);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);

    }

    /**
     * 异步通知接口
     */

    public function payNotify(){

        //获取参数
        $resultArr = json_decode(request(),true);
        return $resultArr;
    }

    /**
     * 异步通知接口(退款)
     */

    public function refundNotify(){

        //获取参数
        $resultArr = json_decode(request(),true);
        return $resultArr;
    }


}