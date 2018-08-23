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
    protected $notify_url;
    protected $charset;
    protected $sign_type;
    protected $version;

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
        $this->notify_url   = config('reapal.notify_url');
        $this->charset   = config('reapal.charset');
        $this->sign_type   = config('reapal.sign_type');
        $this->version   = config('reapal.version');
    }

    /**
     * 代付提交
     */
    public function agentpay()
    {
        $reapalMap = new ReapalUtils();

        $nowTime = Carbon::now();

        $merchantId = request('merchat_id')?request('merchat_id'):$this->merchantId;

        $paramArr = array(
            'charset' => $this->charset,
            'notify_url' => $this->notify_url,
            'trans_time' => $nowTime,
            'batch_no' => request('batch_no'),
            'batch_count' => request('batch_count'),
            'batch_amount' => request('batch_amount'),
            'pay_type' => request('pay_type'),
            'content' => request('content'),
        );

        $url = $this->dsfUrl.'agentpay/pay';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->version,$this->sign_type);
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
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->version);
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
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->version);
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
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId,$this->version);
        $response = json_decode($result,true);
        $encryptkey = $reapalMap->RSADecryptkey($response['encryptkey'],$this->merchantPrivateKey);
        return $reapalMap->AESDecryptResponse($encryptkey,$response['data']);
    }

    /**
     * 预支付接口, 返回调起微信支付需要的参数
     */
    public function prepay()
    {

    }

    /**
     * 支付通知接口
     */
    public function payNotify()
    {

    }
}