<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once 'util.php'; 
require_once 'config.php'; 

//参数数组
$paramArr = array(
            
            'seller_email'=> $seller_email,
			'merchant_id' => $merchant_id,
			'body' => 'yyyy',
			'title' => 'yyyy',
			'order_no' => $_REQUEST['order_no'],
			'total_fee' => $_REQUEST['total_fee'],
			
			'notify_url' => $notify_url,
			'transtime' => '2015-10-16 14:05:00',
			'currency' => '156',
			'member_ip' => '192.168.1.1',
			'terminal_type' => 'mobile',
			'terminal_info' => 'terminal_info',
			'token_id' => '154545487879852200',
			
			'client_type'=>$_REQUEST['client_type'], 
			'user_id'=>$_REQUEST['user_id'], 
	        'appid_source'=>$_REQUEST['appid_source'], 
	        'store_phone'=>$_REQUEST['store_phone'],
			'store_name'=>$_REQUEST['store_name'],
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
?>