<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/29/029
 * Time: 17:28
 */
namespace App\Support\AgentPay;

use App\Modules\Settlement\SettlementPlatform;
use App\Modules\Settlement\SettlementPlatformKuaiQianBatch;
use Illuminate\Support\Facades\Log;

class KuaiQian extends AgentPayBase
{


    public $pubkey_path = '';//快钱公钥地址

    public $pfx_path = '';//商户PFX证书地址
    public $key_password = '';//证书密码
    public $membercode = '';//商户号
    public $merchant_name = '';
    public $url = '';//接口地址
    public $balance_query_url = '';//余额查询接口地址

    public $status_val = [
        101 => '进行中',
        111 => '出款成功',
        112 => '出款失败',
        114 => '已经退款',
    ];


    public function __construct()
    {
        $this->_class_name = basename(__CLASS__);
        //parent::__construct();

//        $this->pubkey_path = app_path('/Support/AgentPay/KuaiQian/99bill.cert.rsa.20340630_sandbox.cer');//快钱公钥地址
//        $this->pfx_path = app_path('/Support/AgentPay/KuaiQian/99bill-rsa.pfx');//商户PFX证书地址
//        $this->url = 'https://sandbox.99bill.com/fo-batch-settlement/services';//测试接口地址
//        $this->key_password = '123456';//测试证书密码
//        $this->membercode = '10012138842';//测试商户号
//        $this->merchant_name = '测试商户';
        $this->pubkey_path = app_path('/Support/AgentPay/KuaiQian/99bill.cert.rsa.20340630.cer');//快钱公钥地址
        $this->pfx_path = app_path('/Support/AgentPay/KuaiQian/99bill-rsa.pfx');//商户PFX证书地址
        $this->url = 'https://www.99bill.com/fo-batch-settlement/services';//正式接口地址
        $this->key_password = 'daqian111';//正式证书密码
        $this->membercode = '10210075284';//正式商户号
        $this->merchant_name = '深圳大千生活科技有限公司';

        $this->balance_query_url = 'http://sandbox.99bill.com/apiservices/services/balance.wsdl';

    }

    public function balanceQuery()
    {
        $memberCode='10012138842';
        $acctType='1';
        $memberAcctCode='';
        $merchantMemberCode='10012138842';
        $key='XSD889YSFS37NZWS';

        $originalData= '&memberCode='.$memberCode.'&acctType='.$acctType.'&memberAcctCode='.$memberAcctCode.'&merchantMemberCode='.$merchantMemberCode.'&key='.$key;

        $autokey = rand(10000000,99999999).rand(10000000,99999999); //随机KEY

        $signeddata = $this->crypto_seal_private($originalData);//私钥加密（验签/OPENSSL_ALGO_SHA1）
        $digitalenvelope = $this->crypto_seal_pubilc($autokey);//公钥加密（数字信封/OPENSSL_PKCS1_PADDING）
        $encrypteddata = $this->encrypt_aes($originalData,$autokey);//数据加密（AES/CBC/PKCS5Padding）

        $url2 = 'http://sandbox.99bill.com/apiservices/services/balance.wsdl';//提交地址

        $str= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:bal="http://www.99bill.com/schema/ma/mbrinfo/balance" xmlns:mbr="http://www.99bill.com/schema/ma/mbrinfo" xmlns:com="http://www.99bill.com/schema/commons">
   <soapenv:Header/>
   <soapenv:Body>
      <bal:balance-request>
         <bal:request-header>
            <mbr:version>
               <com:version>1</com:version>
               <com:service>ma.mbrinfo.balance</com:service>
            </mbr:version>
            <mbr:requestId/>
            <mbr:appId/>
         </bal:request-header>
         <bal:request-body>
            <bal:balanceApiRequestType>
               <!--You have a CHOICE of the next 2 items at this level-->
               <bal:memberCode>'.$memberCode.'</bal:memberCode>
               <bal:acctType>'.$acctType.'</bal:acctType>
               <bal:merchantMemberCode>'.$merchantMemberCode.'</bal:merchantMemberCode>
            </bal:balanceApiRequestType>
            <!--Optional:-->
            <bal:maSealPkiDataType>
               <!--You may enter the following 4 items in any order-->
               <!--Optional:-->
               <mbr:signedData>'.$signeddata.'</mbr:signedData>
               <mbr:encryptedData>'.$encrypteddata.'</mbr:encryptedData>
               <mbr:digitalEnvelope>'.$digitalenvelope.'</mbr:digitalEnvelope>
            </bal:maSealPkiDataType>
         </bal:request-body>
      </bal:balance-request>
   </soapenv:Body>
</soapenv:Envelope>';

        $header[] = "Content-type: text/xml;charset=utf-8";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        $output = curl_exec($ch);

        dd($output);
        $dom = new \DOMDocument();
        $dom -> loadXML($output);
        $receive = array(
            'errorCode' => $dom -> getElementsByTagName('errorCode')->item(0)->nodeValue,//错误编号
            'errorMsg' => $dom -> getElementsByTagName('errorMsg')->item(0)->nodeValue,//错误代码
            'amount' => $dom -> getElementsByTagName('amount')->item(0)->nodeValue,//金额
            'signedData' => $dom -> getElementsByTagName('signedData')->item(0)->nodeValue,//验签
            'encryptedData' => $dom -> getElementsByTagName('encryptedData')->item(0)->nodeValue,//加密报文
            'digitalEnvelope' => $dom -> getElementsByTagName('digitalEnvelope')->item(0)->nodeValue//数字信封
        );

        echo "<br/>".$receive['errorCode'];//错误编号
        echo "<br/>".$receive['errorMsg'];//错误代码

        $receivekey = $this->crypto_unseal_private($receive['digitalEnvelope']);
        $receiveData = $this->decrypt_aes($receive['encryptedData'],$receivekey);

        echo "<br/>".$receiveData;//数据结果

        $ok = crypto_unseal_pubilc($receive['signedData'],$receiveData);
        echo "<br/>ok=".$ok;//验签结果
    }

    public function queryByBatchNo(SettlementPlatformKuaiQianBatch $batch)
    {
        $membercode = $this->membercode;//商户号
        $time1 = date('YmdHis');//时间
        $batchno= $batch->batch_no;//批次号

//request-header
        $rheader = '<tns:batchid-query-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.batchidquery</ns0:service>
    </tns:version>
    <ns1:time>'.$time1.'</ns1:time>
  </tns:request-header>';

//request-body

        $rbody = '
<tns:request-body>
    <tns:batch-no>'.$batchno.'</tns:batch-no>
    <tns:page>1</tns:page>
    <tns:page-size>1000</tns:page-size>
    <tns:list-flag>0</tns:list-flag>
  </tns:request-body>
</tns:batchid-query-request>';

        $originalData = $rheader.$rbody;//原始报文
        $autokey = rand(10000000,99999999).rand(10000000,99999999); //随机KEY
        $originalData = gzencode($originalData);//GZIP压缩报文
        $signeddata = $this->crypto_seal_private($originalData);//私钥加密（验签/OPENSSL_ALGO_SHA1）
        $digitalenvelope = $this->crypto_seal_pubilc($autokey);//公钥加密（数字信封/OPENSSL_PKCS1_PADDING）
        $encrypteddata = $this->encrypt_aes($originalData,$autokey);//数据加密（AES/CBC/PKCS5Padding）


//提交报文
        $str= '<?xml version=\'1.0\' encoding=\'UTF-8\'?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Body><tns:settlement-pki-api-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.batchidquery</ns0:service>
    </tns:version>
    <ns1:time>'.$time1.'</ns1:time>
  </tns:request-header>
  <tns:request-body>
    <tns:member-code>'.$membercode.'</tns:member-code>
    <tns:data>
      <ns1:original-data/>
      <ns1:signed-data>'.$signeddata.'</ns1:signed-data>
      <ns1:encrypted-data>'.$encrypteddata.'</ns1:encrypted-data>
      <ns1:digital-envelope>'.$digitalenvelope.'</ns1:digital-envelope>
    </tns:data>
  </tns:request-body>
</tns:settlement-pki-api-request></soapenv:Body></soapenv:Envelope>';

        $output = $this->curl_post($str);

        $dom = new \DOMDocument();
        $dom -> loadXML($output);
        $receive = array(
            'membercode' => $dom -> getElementsByTagName('member-code')->item(0)->nodeValue,//商户号
            'status' => $dom -> getElementsByTagName('status')->item(0)->nodeValue,//状态
            'errorCode' => $dom -> getElementsByTagName('error-code')->item(0)->nodeValue,//错误编号
            'errorMsg' => $dom -> getElementsByTagName('error-msg')->item(0)->nodeValue,//错误代码
            'signedData' => $dom -> getElementsByTagName('signed-data')->item(0)->nodeValue,//验签
            'encryptedData' => $dom -> getElementsByTagName('encrypted-data')->item(0)->nodeValue,//加密报文
            'digitalEnvelope' => $dom -> getElementsByTagName('digital-envelope')->item(0)->nodeValue//数字信封
        );

        $data_query = '';
        $data_query .= "付款商户号：".$receive['membercode'];//商户号
        $data_query .= "<br/>批次号：".$batchno;//批次号
        $data_query .= "<br/>应答状态：".$receive['status'];//批次状态
        $data_query .= "<br/>错误编号：".$receive['errorCode'];//错误编号
        $data_query .= "<br/>错误代码：".$receive['errorMsg'];//错误代码

        if ($receive['errorCode'] != '0000') {
            $batch->status = SettlementPlatformKuaiQianBatch::STATUS_FAILED;
            $batch->data_query = $data_query;
            $batch->save();
            return $batch;
        }
        $receivekey = $this->crypto_unseal_private($receive['digitalEnvelope']);

        $receiveData2 = $this->decrypt_aes($receive['encryptedData'],$receivekey);

        $receiveData = gzdecode($receiveData2);


        $data_query .= "<br/>结果明细：<br/>";//数据结果
        $data_query .= "<textarea rows=\"30\" cols=\"100\">".$receiveData."</textarea>";

        $ok = $this->crypto_unseal_pubilc($receive['signedData'],$receiveData2);

        if ($ok == 1) {
            $data_query .= "<br/>验签成功！";
            $batch->data_query = $data_query;

            $rt = $this->loadDetail($receiveData);
            if ($rt === true) {
                $batch->status = SettlementPlatformKuaiQianBatch::STATUS_FINISHED;
            }
            $batch->save();
        } else {
            $data_query .= "<br/>验签失败！";
            $batch->data_query = $data_query;
            $batch->save();
        }

        return $batch;
    }

    /**
     * 解析处理结果明细
     * @param $receiveData
     */
    public function loadDetail($receiveData)
    {

        if (empty($receiveData)) {
            return false;
        }
        $receiveData = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Body>'
            . $receiveData
            . '</soapenv:Body></soapenv:Envelope>';

        $dom = new \DOMDocument();
        $dom->loadXML($receiveData);
        $items = $dom->getElementsByTagName('pay2bank-result');
        if (empty($items)) {
            return false;
        }
        $has_101 = false;//是否有101 处理中状态的，没有101的代表整个批次处理完成
        foreach ($items as $k=>$val) {
            $error_code = $val->getElementsByTagName('error-code')->item(0)->nodeValue;
            $error_msg = $val->getElementsByTagName('error-msg')->item(0)->nodeValue;
            $settlement_no = $val->getElementsByTagName('merchant-id')->item(0)->nodeValue;
            $status = $val->getElementsByTagName('status')->item(0)->nodeValue;
            if (!empty($error_code) && $error_code != '0000') {
                $update = [
                    'status' => SettlementPlatform::STATUS_FAIL,
                    'reason' => $error_code . ':'.$error_msg
                ];
                SettlementPlatform::where('settlement_no',$settlement_no)
                    ->update($update);
            } else {
                switch ($status) {
                    case 101:
                        $has_101 = true;
                        break;
                    case 111:
                        $update = [
                            'status' => SettlementPlatform::STATUS_PAID,
                        ];
                        SettlementPlatform::where('settlement_no',$settlement_no)
                            ->update($update);
                        break;
                    case 112:
                        $update = [
                            'status' => SettlementPlatform::STATUS_FAIL,
                            'reason' => '打款失败'
                        ];
                        SettlementPlatform::where('settlement_no',$settlement_no)
                            ->update($update);
                        break;
                    case 114:
                        $update = [
                            'status' => SettlementPlatform::STATUS_FAIL,
                            'reason' => '已退款'
                        ];
                        SettlementPlatform::where('settlement_no',$settlement_no)
                            ->update($update);
                        break;
                    default:
                        break;
                }
            }

        }
        return !$has_101;
    }

    public function send(SettlementPlatformKuaiQianBatch $batch)
    {
        $originalData = $batch->data_send;
        $autokey = rand(10000000,99999999).rand(10000000,99999999); //随机KEY
        $originalData = gzencode($originalData);//GZIP压缩报文
        $signeddata = $this->crypto_seal_private($originalData);//私钥加密（验签/OPENSSL_ALGO_SHA1）
        $digitalenvelope = $this->crypto_seal_pubilc($autokey);//公钥加密（数字信封/OPENSSL_PKCS1_PADDING）
        $encrypteddata = $this->encrypt_aes($originalData,$autokey);//数据加密（AES/CBC/PKCS5Padding）


//提交报文
        $str= '<?xml version=\'1.0\' encoding=\'UTF-8\'?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Body>
        <tns:settlement-pki-api-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.pay</ns0:service>
    </tns:version>
    <ns1:time>'.date('YmdHis').'</ns1:time>
  </tns:request-header>
  <tns:request-body>
    <tns:member-code>'.$this->membercode.'</tns:member-code>
    <tns:data>
      <ns1:original-data/>
      <ns1:signed-data>'.$signeddata.'</ns1:signed-data>
      <ns1:encrypted-data>'.$encrypteddata.'</ns1:encrypted-data>
      <ns1:digital-envelope>'.$digitalenvelope.'</ns1:digital-envelope>
    </tns:data>
  </tns:request-body>
</tns:settlement-pki-api-request></soapenv:Body></soapenv:Envelope>';


        $output = $this->curl_post($str);
        $dom = new \DOMDocument();
        $dom->loadXML($output);
        $receive = array(
            'membercode' => $dom->getElementsByTagName('member-code')->item(0)->nodeValue,//商户号
            'status' => $dom->getElementsByTagName('status')->item(0)->nodeValue,//状态
            'errorCode' => $dom->getElementsByTagName('error-code')->item(0)->nodeValue,//错误编号
            'errorMsg' => $dom->getElementsByTagName('error-msg')->item(0)->nodeValue,//错误代码
            'signedData' => $dom->getElementsByTagName('signed-data')->item(0)->nodeValue,//验签
            'encryptedData' => $dom->getElementsByTagName('encrypted-data')->item(0)->nodeValue,//加密报文
            'digitalEnvelope' => $dom->getElementsByTagName('digital-envelope')->item(0)->nodeValue//数字信封
        );

        $data_receive = '';
        $data_receive .= "付款商户号：".$receive['membercode'];//商户号
        $data_receive .=  "<br/>应答状态：".$receive['status'];//批次状态
        $data_receive .=  "<br/>错误编号：".$receive['errorCode'];//错误编号
        $data_receive .=  "<br/>错误代码：".$receive['errorMsg'];//错误代码

        if ($receive['errorCode'] != '0000') {
            $batch->status = SettlementPlatformKuaiQianBatch::STATUS_FAILED;
            $batch->data_receive = $data_receive;
            $batch->save();
            return $batch;
        }

        $receivekey = $this->crypto_unseal_private($receive['digitalEnvelope']);
        $receiveData2 = $this->decrypt_aes($receive['encryptedData'],$receivekey);
        $receiveData = $this->gzdecode($receiveData2);

        $data_receive .=  "<br/>结果明细：<br/>";//数据结果
        $data_receive .=  "<textarea rows=\"30\" cols=\"100\">".$receiveData."</textarea>";

        $ok = $this->crypto_unseal_pubilc($receive['signedData'],$receiveData2);

        if($ok==1) {
            $data_receive .=  "<br/><br/>验签成功！";
            $this->loadDetail($receiveData);

        } else {
            $data_receive.=  "<br/><br/>验签失败！";
        }
        $batch->status = SettlementPlatformKuaiQianBatch::STATUS_SENDED;
        $batch->data_receive = $data_receive;
        $batch->save();
        return $batch;
    }


    /**
     * 生成打款数据
     * @param $settlement_platform 结算单
     * @param $batch_no 批次号
     * @param bool $again 是否是第二次生成
     * @return bool
     */
    public function genXmlSend($settlement_platform, $batch_no, $again = false)
    {
        if (empty($settlement_platform)) {
            return false;
        }

        $time1 = date('YmdHis');//时间戳

        /**批次信息**/

        /** 商户编号 */
        $memberCode = $this->membercode;
        /** 付款方帐号 */
        $payerAcctCode = $memberCode."01";
        /** 批次号 */
        $batchNo = $batch_no;
        /** 发起日期 */
        $applyDate = $time1;
        /** 付款商户名称 */
        $merchantName = $this->merchant_name;
        /** 总笔额 */
        $totalCnt = 0;
        /** 总金额 */
        $totalAmt = 0;
        /** 付费方式 0:收款方付款;1:付款方付费 */
        $feeType = "1";
        /** 币种 */
        $cur = "RMB";
        /** 是否验证金额 0:验证; 1:不验证*/
        $checkAmtCnt = "0";
        /** 是否整批失败 0:整批失败; 1:不整批失败*/
        $batchFail = "1";
        /** 充值方式 0:代扣，1:充值，2:垫资*/
        $rechargeType = "1";
        /** 是否自动退款 0:自动退款; 1:不自动退款*/
        $autoRefund = "0";
        /** 是否短信通知 0:通知; 1:不通知*/
        $phoneNoteFlag = "0";
        /** 预留字段1 */
        $merchantMemo1 = "";
        /** 预留字段2 */
        $merchantMemo2 = "";
        /** 预留字段3 */
        $merchantMemo3 = "";


//request-header
        $rheader = '<tns:batch-settlement-apply-request xmlns:ns0="http://www.99bill.com/schema/commons" xmlns:ns1="http://www.99bill.com/schema/fo/commons" xmlns:tns="http://www.99bill.com/schema/fo/settlement">
  <tns:request-header>
    <tns:version xmlns:tns="http://www.99bill.com/schema/fo/commons">
      <ns0:version>1.0.1</ns0:version>
      <ns0:service>fo.batch.settlement.pay</ns0:service>
    </tns:version>
    <ns1:time>'.$time1.'</ns1:time>
  </tns:request-header>';

//rdetail
        $rdetail = '';

        $settlement_platform_ids = [];//生成结算单的id号
        $settlement_platform->each(function ($item) use (&$rdetail, &$totalCnt, &$totalAmt,$batchNo, &$settlement_platform_ids, $again) {

            $sub_bank_name = explode('|',$item->sub_bank_name);
            $bank_open_address = explode('|',$item->bank_open_address);
            if (count($bank_open_address) != 2) {
                $item->status = SettlementPlatform::STATUS_FAIL;
                $item->reason = '结算单地址有误';
                $item->save();
                Log::error('结算单地址有误' . $item->settlement_no);
            }
            $bank_open_address = explode(',',$bank_open_address[0]);

            $amount = intval($item->real_amount *100);//换算成分
            if ($amount==0) {
                $item->status = SettlementPlatform::STATUS_FAIL;
                $item->reason = '结算单金额为零';
                $item->save();
                Log::error('结算单金额为零' . $item->settlement_no);
            } elseif (count($sub_bank_name) != 2 || count($bank_open_address) < 2) {
                $item->status = SettlementPlatform::STATUS_FAIL;
                $item->reason = '结算单地址或者支行信息有误';
                $item->save();
                Log::error('结算单地址或者支行信息有误' . $item->settlement_no);

            } else {
                if ($again) {
                    $item->pay_again_batch_no = $batchNo;
                    $item->status = SettlementPlatform::STATUS_RE_PAY;
                } else {
                    $item->pay_batch_no = $batchNo;
                    $item->status = SettlementPlatform::STATUS_PAYING;
                }

                $item->save();

                $totalCnt ++;
                $totalAmt += $amount;

                $bank_card_type = $item->bank_card_type-1;//块钱是 0公司1个人 平台是 1公司2个人
                $settlement_platform_ids[] = $item->id;
                $memo = $amount > 5000000?'代付金额超过5w':'';
                $rdetail = $rdetail.'
<tns:pay2bank>
        <ns1:merchant-id>'.$item->settlement_no.'</ns1:merchant-id>
        <ns1:amt>'.$amount.'</ns1:amt>
        <ns1:bank>'.$sub_bank_name[0].'</ns1:bank>
        <ns1:name>'.$item->bank_open_name.'</ns1:name>
        <ns1:bank-card-no>'.$item->bank_card_no.'</ns1:bank-card-no>
        <ns1:branch-bank>'.$sub_bank_name[1].'</ns1:branch-bank>
        <ns1:payee-type>'.$bank_card_type.'</ns1:payee-type>
        <ns1:province>'.$bank_open_address[0].'</ns1:province>
        <ns1:city>'.$bank_open_address[1].'</ns1:city>
        <ns1:memo>'.$memo.'</ns1:memo>
        <ns1:bank-purpose></ns1:bank-purpose>
        <ns1:bank-memo></ns1:bank-memo>
        <ns1:payee-note></ns1:payee-note>
        <ns1:payee-mobile></ns1:payee-mobile>
        <ns1:payee-email></ns1:payee-email>
        <ns1:period/>
        <ns1:merchant-memo1></ns1:merchant-memo1>
        <ns1:merchant-memo2></ns1:merchant-memo2>
        <ns1:merchant-memo3></ns1:merchant-memo3>
      </tns:pay2bank>';
            }

        });



//request-body
        $rbody = '
<tns:request-body>
    <tns:payer-acctCode>'.$payerAcctCode.'</tns:payer-acctCode>
    <tns:batch-no>'.$batchNo.'</tns:batch-no>
    <tns:apply-date>'.$time1.'</tns:apply-date>
    <tns:name>'.$merchantName.'</tns:name>
    <tns:total-amt>'.$totalAmt.'</tns:total-amt>
    <tns:total-cnt>'.$totalCnt.'</tns:total-cnt>
    <tns:fee-type>'.$feeType.'</tns:fee-type>
    <tns:cur>'.$cur.'</tns:cur>
    <tns:checkAmt-cnt>'.$checkAmtCnt.'</tns:checkAmt-cnt>
    <tns:batch-fail>'.$batchFail.'</tns:batch-fail>
    <tns:recharge-type>'.$rechargeType.'</tns:recharge-type>
    <tns:auto-refund>'.$autoRefund.'</tns:auto-refund>
    <tns:phoneNote-flag>'.$phoneNoteFlag.'</tns:phoneNote-flag>
    <tns:merchant-memo1>'.$merchantMemo1.'</tns:merchant-memo1>
    <tns:merchant-memo2>'.$merchantMemo2.'</tns:merchant-memo2>
    <tns:merchant-memo3>'.$merchantMemo3.'</tns:merchant-memo3>
    <tns:pay2bank-list>'.$rdetail.'
	</tns:pay2bank-list>
  </tns:request-body>
</tns:batch-settlement-apply-request>';

        $originalData = $rheader.$rbody;//原始报文

        return ['originalData'=>$originalData, 'settlement_platform_ids'=>$settlement_platform_ids, 'amount'=>$totalAmt];

    }


    //私钥加密RSAwithSHA1
    public function crypto_seal_private($original_data){
        $pfx_path = $this->pfx_path;
        $key_password = $this->key_password;
        $sealed_data=array();
        $pfx_file='file://'.$pfx_path;
        $pfx=file_get_contents($pfx_file);
        $certs=array();
        openssl_pkcs12_read($pfx,$certs,$key_password);
        $privkey=$certs['pkey'];
        openssl_sign($original_data,$signature,$privkey,OPENSSL_ALGO_SHA1);
        return base64_encode($signature);
    }

//公钥加密OPENSSL_PKCS1_PADDING
    public function crypto_seal_pubilc($original_data){
        $pubkey_path = $this->pubkey_path;
        $sealed_data=array();
        $pubkey_file=$pubkey_path;
        $pubkey=file_get_contents("file://".$pubkey_file);
        openssl_public_encrypt($original_data,$signature,$pubkey,OPENSSL_PKCS1_PADDING);
        return base64_encode($signature);
    }

//私钥解密OPENSSL_PKCS1_PADDING
    public function crypto_unseal_private($digitalEnvelope){
        $pfx_path = $this->pfx_path;
        $key_password = $this->key_password;
        $pfx_file='file://'.$pfx_path;
        $pfx=file_get_contents($pfx_file);
        $certs=array();
        openssl_pkcs12_read($pfx,$certs,$key_password);
        $privkey=$certs['pkey'];
        $digitalEnvelope = base64_decode($digitalEnvelope);
        $aaa= openssl_private_decrypt($digitalEnvelope,$receivekey,$privkey,OPENSSL_PKCS1_PADDING);
        return $receivekey;
    }

//AES加密
    public function encrypt_aes($encrypt,$key){
        $size = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $encrypt = $this->pkcs5Pad($encrypt,$size);
        $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
        $passcrypt = @mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $encrypt, MCRYPT_MODE_CBC,$iv);
        $encode = base64_encode($passcrypt);
        return $encode;
    }



//pkcs5加密
    public function pkcs5Pad($text,$blocksize){
        $pad = $blocksize-(strlen($text)%$blocksize);
        return $text.str_repeat(chr($pad),$pad);
    }


//AES解密
    public function decrypt_aes($str,$key) {
        $str =  base64_decode($str);
        $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
        $or_data = @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC,$iv);
        $str = $this->pkcs5Unpad($or_data);
        return $str;
    }


//pcks5解密
    public function pkcs5Unpad($text) {
        $pad = ord($text{strlen($text)-1});
        if ($pad>strlen($text))
            return false;
        if (strspn($text,chr($pad),strlen($text)-$pad)!=$pad)
            return false;
        return substr($text,0,-1*$pad);
    }

//公钥验签
    public function crypto_unseal_pubilc($signedData,$receiveData){
        $pubkey_path = $this->pubkey_path;
        $MAC = base64_decode($signedData);
        $fp = fopen($pubkey_path, "r");
        $cert = fread($fp, 8192);
        fclose($fp);
        $pubkeyid = openssl_get_publickey($cert);
        $ok = openssl_verify($receiveData, $MAC, $pubkeyid);
        return $ok;
    }


//gzip解密
    public function gzdecode($data)
    {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4)
        {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8)
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16)
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2)
            $headerlen += 2;
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE)
            $unpacked = $data;
        return $unpacked;
    }

    public function curl_post($str, $url='')
    {
        if (empty($url)) {
            $url = $this->url;
        }
        $header[] = "Content-type: text/xml;charset=utf-8";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        $output = curl_exec($ch);
        return $output;
    }
}