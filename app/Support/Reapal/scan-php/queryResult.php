<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once 'util.php'; 
require_once 'config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $_REQUEST['merchant_id'],
     'order_no' => $_REQUEST['order_no']    );
//$url = 'http://api.reapal.com/qrcode/scan/encryptSearch';
$url = $apiUrl.'/qrcode/scan/encryptSearch';
//$url = 'http://api.reapal.com/fast/search';
echo $url,"\n";

$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo $encryptkey,"\n";
echo AESDecryptResponse($encryptkey,$response['data']);
?>