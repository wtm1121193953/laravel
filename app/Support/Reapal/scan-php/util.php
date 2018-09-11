<?php

//签名函数
function createSign ($paramArr,$apiKey) {
     global $appSecret;
     $sign = $appSecret;
     ksort($paramArr);
     foreach ($paramArr as $key => $val) {
         if ($key != '' && $val != '') {
             $sign .= $key.'='.$val.'&';
         }
     }
    
     $sign = substr ( $sign,0,(strlen ( $sign )-1));
     $sign.=$appSecret;
     $sign = md5($sign.$apiKey);
     return $sign;
}
	/**
	 * 生成一个随机的字符串作为AES密钥
	 * 
	 * @param number $length
	 * @return string
	 */
 function generateAESKey($length=16){
		$baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$AESKey = '';
		$_len = strlen($baseString);
		for($i=1;$i<=$length;$i++){
			$AESKey .= $baseString[rand(0, $_len-1)];
		}
		return $AESKey;
	}
/**
	 * 通过RSA，使用融宝公钥，加密本次请求的AESKey
	 * 
	 * @return string
	 */
 function RSAEncryptkey($encryptKey,$reapalPublicKey){
	   $public_key= getPublicKey($reapalPublicKey); 
	
	   $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的  
	
       openssl_public_encrypt($encryptKey,$encrypted,$pu_key);//公钥加密  
 
 return base64_encode($encrypted); 
	
	  

	}
	/**
	 * 通过RSA，使用融宝公钥，加密本次请求的AESKey
	 * 
	 * @return string
	 */
 function RSADecryptkey($encryptKey,$merchantPrivateKey){
	 $private_key= getPrivateKey($merchantPrivateKey); 

     $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
	
	 openssl_private_decrypt(base64_decode($encryptKey),$decrypted,$pi_key);//私钥解密
	return $decrypted;
	  

	}
/**
	 * 通过AES加密请求数据
	 * 
	 * @param array $query
	 * @return string
	 */
 function AESEncryptRequest($encryptKey,array $query){
 	
		return encrypt(json_encode($query),$encryptKey);
		
	}
	/**
	 * 通过AES解密请求数据
	 * 
	 * @param array $query
	 * @return string
	 */
 function AESDecryptResponse($encryptKey,$data){
		return decrypt($data,$encryptKey);
		
	}
 function encrypt($input, $key) {
	$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
	$input = pkcs5_pad($input, $size);
	$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
	$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	$data = base64_encode($data);
	return $data;
	}
 
 function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
  function decrypt($sStr, $sKey) {
		$decrypted= mcrypt_decrypt(
		MCRYPT_RIJNDAEL_128,
		$sKey,
		base64_decode($sStr),
		MCRYPT_MODE_ECB
	);
 
		$dec_s = strlen($decrypted);
		$padding = ord($decrypted[$dec_s-1]);
		$decrypted = substr($decrypted, 0, -$padding);
		return $decrypted;
	}	

function getPrivateKey($cert_path) {
	$pkcs12 = file_get_contents ( $cert_path );
	
	return $pkcs12;
}
function getPublicKey($cert_path) {
	$pkcs12 = file_get_contents ( $cert_path );
		return $pkcs12;
}
function sendHttpRequest($params, $url) {
	$opts = getRequestParamString ( $params );
	echo $opts;
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false);//不验证证书
  curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false);//不验证HOST
	curl_setopt ( $ch, CURLOPT_SSLVERSION, 3);
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
			'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
	) );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );
	
	/**
	 * 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
	 */
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	
	// 运行cURL，请求网页
	$html = curl_exec ( $ch );
	// close cURL resource, and free up system resources
	curl_close ( $ch );
	return $html;
}

/**
 * 组装报文
 *
 * @param unknown_type $params        	
 * @return string
 */
function getRequestParamString($params) {
	$params_str = '';
	foreach ( $params as $key => $value ) {
		$params_str .= ($key . '=' . (!isset ( $value ) ? '' : urlencode( $value )) . '&');
	}
	return substr ( $params_str, 0, strlen ( $params_str ) - 1 );
}


function send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id){
	
	//生成签名
	$sign = createSign($paramArr,$apiKey);
    
	$paramArr['sign'] = $sign;
	//生成AESkey
	$generateAESKey = generateAESKey();
	$request = array();
	$request['merchant_id'] = $merchant_id;
	//加密key
	$request['encryptkey'] = RSAEncryptkey($generateAESKey,$reapalPublicKey);
	//加密数据
	$request['data'] = AESEncryptRequest($generateAESKey,$paramArr);
	
	return sendHttpRequest($request,$url);
}
?>