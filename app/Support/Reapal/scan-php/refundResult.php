<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once 'util.php'; 
require_once 'config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $_REQUEST['merchant_id'],
	 'refund_order_no' => $_REQUEST['refund_order_no'],
	 'order_no' => $_REQUEST['order_no'],
	 'amount' => $_REQUEST['amount'],
	
);

$url = 'http://api.reapal.com/qrcode/scan/encryptRefundApply';
$url = $apiUrl.'/qrcode/scan/encryptRefundApply';
//$url = 'http://api.reapal.com/fast/search';
echo $url,"\n";

$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);

$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo $encryptkey,"\n";
echo AESDecryptResponse($encryptkey,$response['data']);
?>






