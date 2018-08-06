<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/3
 * Time: 14:15
 */

namespace App\Support;

use App\Support\Common;

class TpsApi
{

    public static function sendEmail($to, $title, $content)
    {
        // todo 发送邮件
    }

    /**
     * 创建账号
     * @param $account string 账号
     * @param $password string 密码
     * @param string $parentAccount 父级账号, 运营中心创建账号时为空
     * @param int $type 账号类型 1-运营中心账号  2- 商户创建
     */
    public static function createTpsAccount($account, $password, $parentAccount='', $type=1)
    {
        // todo
        $data = array(
            'account' => $account,
            'password' => $password,
            'from' => $type,
            'p_account' =>$parentAccount
        );
        $key = config('tpsapi.key');
        
        $enc_data = TpsApi::api_encrypt(json_encode($data),$key);
        $enc_token = TpsApi::api_encrypt($key,$key);
        
        $post_data = array(
            'token' => $enc_token,
            'data' => $enc_data,
        );
        
        $url = config('tpsapi.register_url');
        
        $result = Common::curl_post($url,$post_data);
        return var_dump($result);

    }

    /**
     * 验证账号是否正确
     * @param $account
     * @param $password
     */
    public static function checkTpsAccount($account, $password)
    {
        // todo
    }
    
    /**
     * 加密函数
     * @param $string
     * @param $key
     * return string
     */
    public static function api_encrypt($string,$key='') {
        
        $encryptKey = md5($key);
        $keyLen = strlen($encryptKey);
        $data = substr(md5($string.$encryptKey), 0, 8).$string;
        $dataLen = strlen($data);
        $rndkey = array();
        $box = array();
        $cipherText = "";
        for ($i = 0; $i < 256; $i++) {
            $rndkey[$i] = ord($encryptKey[$i % $keyLen]);
            $box[$i] = $i;
        }
        for ($i = 0, $j = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($i = 0, $j = 0, $k = 0; $i < $dataLen; $i++) {
            $k = ($k + 1) % 256;
            $j = ($j + $box[$k]) % 256;
            $tmp = $box[$k];
            $box[$k] = $box[$j];
            $box[$j] = $tmp;
            $cipherText .= chr(ord($data[$i]) ^ ($box[($box[$k] + $box[$j]) % 256]));
        }
        return str_replace('=', '', base64_encode($cipherText));
    }

}