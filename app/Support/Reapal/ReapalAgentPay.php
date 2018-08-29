<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/8/29/029
 * Time: 11:27
 */

namespace App\Support\Reapal;


class ReapalAgentPay
{

    protected $merchantId;
    protected $sellerEmail;
    protected $merchantPrivateKey;
    protected $reapalPublicKey;
    protected $apiKey;
    protected $charset;

    protected $dsfUrl;
    protected $dsf_sign_type;
    protected $dsfVersion;

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

        $this->apiKey = config('reapal.apiKey');
        $this->dsfUrl = config('reapal.dsfUrl');
        $this->charset = config('reapal.charset');
        $this->dsf_sign_type = config('reapal.dsf_sign_type');
        $this->dsfVersion = config('reapal.dsf_version');

    }

    /**
     * 代付提交
     * @param $batch_no
     * @param $batch_count
     * @param $batch_amount
     * @param $content
     * @return string
     */
    public function agentpay($batch_no, $batch_count, $batch_amount, $content)
    {
        $reapalMap = new ReapalUtils();

        $nowTime = date('Y-m-d H:i:s');


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

        $url = $this->dsfUrl . 'agentpay/pay';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $this->merchantId, $this->dsfVersion, $this->dsf_sign_type);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->encryptKey($response['encryptkey'], $this->merchantPrivateKey);
        return $reapalMap->decrypt($encryptkey, $response['data']);

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

        $merchantId = request('merchat_id') ? request('merchat_id') : $this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl . 'agentpay/batchpayquery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId, $this->dsfVersion);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->encryptKey($response['encryptkey'], $this->merchantPrivateKey);
        return $reapalMap->decrypt($encryptkey, $response['data']);

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

        $merchantId = request('merchat_id') ? request('merchat_id') : $this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl . 'agentpay/singlepayquery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId, $this->dsfVersion);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->encryptKey($response['encryptkey'], $this->merchantPrivateKey);

        return $reapalMap->decrypt($encryptkey, $response['data']);
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

        $merchantId = request('merchat_id') ? request('merchat_id') : $this->merchantId;

        $reapalMap = new ReapalUtils();

        $url = $this->dsfUrl . 'agentpay/balancequery';
        $result = $reapalMap->send($paramArr, $url, $this->apiKey, $this->reapalPublicKey, $merchantId, $this->dsfVersion);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->encryptKey($response['encryptkey'], $this->merchantPrivateKey);
        return $reapalMap->decrypt($encryptkey, $response['data']);
    }
}