<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once 'util.php';
require_once 'config.php';


function run(){
//商户ID
    $merchant_id = '100000001304038';
//商户邮箱
    $seller_email = 'daqian@shoptps.com';
// 商户私钥
    $merchantPrivateKey = 'D:\\projects\\daqian-o2o\\resources\\reapal\\cert\\test\\user-rsa.pem';
// 融宝公钥
    $reapalPublicKey = 'D:\\projects\\daqian-o2o\\resources\\reapal\\cert\\itrus001.pem';
// APIKEy
    $apiKey = '3717b57fe2845f6c2f7735624a9d3ce12e22g711809e39618e7d0gffbaf098b5';
// APIUrl
    $apiUrl = 'http://api.reapal.com';

//版本号
    $version = '3.1.3';
    $notify_url='https://www.baidu.com/';
    $return_url='https://www.baidu.com/';
//参数数组
    $paramArr = array(

        'seller_email'=> $seller_email,
        'merchant_id' => $merchant_id,
        'body' => 'yyyy',
        'title' => 'yyyy',
        'order_no' => 'O20180828165417472969',
        'total_fee' => '100',

        'notify_url' => $notify_url,
        'transtime' => '2015-10-16 14:05:00',
        'currency' => '156',
        'member_ip' => '192.168.1.1',
        'terminal_type' => 'mobile',
        'terminal_info' => 'terminal_info',
        'token_id' => '154545487879852200',

        'client_type'=>0,
        'user_id'=>'mock_open_id',
        'appid_source'=>'wx8d0f5e945df699c2',
        'store_phone'=>'15914021584',
        'store_name'=>'虾庄就是用来吃虾的。天下第一虾了解一下',
        //'merchant_code'=>$_REQUEST['merchant_code'],
        'version'=>'3.1.3',

    );
    $url = $apiUrl.'/qrcode/scan/encryptline';
    echo $url,"\n";
    foreach($paramArr as $key=>$value)
        echo $key."=>".$value."<br/>";
    $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);


    $response = json_decode($result,true);
    $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
    echo $encryptkey,"\n";
    echo AESDecryptResponse($encryptkey,$response['data']);
}

?>