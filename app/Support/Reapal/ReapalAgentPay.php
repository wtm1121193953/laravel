<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/8/29/029
 * Time: 11:27
 */

namespace App\Support\Reapal;


use App\Exceptions\BaseResponseException;
use App\Modules\Settlement\SettlementPayBatch;
use App\Modules\Settlement\SettlementPlatformService;

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

        /*//正式商户ID
        $this->merchantId = config('reapal.merchant_id');
        //正式商户邮箱
        $this->sellerEmail = config('reapal.seller_email');

        //商户私钥
        $this->merchantPrivateKey = config('reapal.merchantPrivateKey');
        //融宝公钥
        $this->reapalPublicKey = config('reapal.reapalPublicKey');

        $this->apiKey = config('reapal.apiKey');
        $this->dsfUrl = config('reapal.dsfUrl');
        $this->charset = config('reapal.charset');
        $this->dsf_sign_type = config('reapal.dsf_sign_type');
        $this->dsfVersion = config('reapal.dsf_version');*/


        //测试商户ID
        $this->merchantId = '100000000000147';
        //商户邮箱
        $this->sellerEmail = config('reapal.seller_email');
        //商户私钥
        $this->merchantPrivateKey = resource_path('reapal/cert/test/itrus001_pri.pem');
        //融宝公钥
        $this->reapalPublicKey = resource_path('reapal/cert/test/itrus001.pem');
        $this->apiKey = 'g0be2385657fa355af68b74e9913a1320af82gb7ae5f580g79bffd04a402ba8f';
        $this->dsfUrl = config('reapal.dsfUrl');
        $this->charset = config('reapal.charset');
        $this->dsf_sign_type = config('reapal.dsf_sign_type');
        $this->dsfVersion = config('reapal.dsf_version');

    }

    protected function apiPost($url, $data)
    {
        $reapalMap = new ReapalUtils();
        $result = $reapalMap->send($data, $url, $this->apiKey, $this->reapalPublicKey, $this->merchantId, $this->dsfVersion, $this->dsf_sign_type);
        $response = json_decode($result, true);
        $encryptkey = $reapalMap->decryptKey($response['encryptkey'], $this->merchantPrivateKey);
        $result = $reapalMap->decrypt($response['data'], $encryptkey);
        $result = json_decode($result, 1);
        return $result;
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
        $nowTime = date('Y-m-d H:i:s');

        $content = '1,62220215080205389633,jack-cooper,工商银行,分行,支行,私,0.01,CNY,北京,北京,18910116131,身份证,420321199202150718,0001,12306,hehe,200100000001422,67180118000001421|2,62220215080205389634,jack,工商银行,分行,支行,私,0.11,CNY,北京,北京,18910116133,身份证,420321199202150728,0002,12307,hehe2,200100000001423,67180118000001422|3,62220215080205389635,cooper,工商银行,分行,支行,私,0.1,CNY,北京,北京,18910116134,身份证,420321199202150729,0003,12308,hehe3,200100000001424,67180118000001423|';

        $paramArr = array(
            'charset' => $this->charset,
            'notify_url' => url('/api/pay/AgentNotify'),
            'trans_time' => $nowTime,
            'batch_no' => $batch_no,
            'batch_count' => $batch_count,
            'batch_amount' => $batch_amount,
            'pay_type' => 1,
            'content' => $content,
        );
        dd($paramArr);

        $url = $this->dsfUrl . 'agentpay/pay';
        $result = $this->apiPost($url, $paramArr);

        $settlement = SettlementPayBatch::where('batch_no', $batch_no)->firstOr();
        if ($result['result_code'] == 0000) {
            $settlement->status = 2;
        } else {
            $settlement->error_code = $result['result_code'];
            $settlement->error_msg = $result['result_msg'];
        }
        $settlement->save();

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

        $url = $this->dsfUrl . 'agentpay/batchpayquery';

        $result = $this->apiPost($url, $paramArr);
        return $result;

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

        $url = $this->dsfUrl . 'agentpay/singlepayquery';
        $result = $this->apiPost($url, $paramArr);
        return $result;
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

        $url = $this->dsfUrl . 'agentpay/balancequery';
        $result = $this->apiPost($url, $paramArr);
        return $result;
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    public function agentNotify()
    {
        //获取参数
        $resultArr = request()->all();
        $reapalMap = new ReapalUtils();
        $encryptkey = $reapalMap->decryptKey($resultArr['encryptkey'], $this->merchantPrivateKey);
        $result = $reapalMap->decrypt($resultArr['data'], $encryptkey);
        $result = json_decode($result, 1);
        return $result;
    }
}

