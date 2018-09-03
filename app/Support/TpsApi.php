<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/3
 * Time: 14:15
 */

namespace App\Support;

use App\Exceptions\BaseResponseException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TpsApi
{

    /**
     * 发送邮件
     * @param $to
     * @param $title
     * @param $content
     * @return array|mixed
     */
    public static function sendEmail($to, $title, $content)
    {
        $data = array(
            'recipients' => $to, //收件人
            'notifications' => '', //抄送人
            'title' => $title, //标题
            'contentTxt' => $content //内容
        );
        $url = config('tpsapi.mail_url');
        $result = self::postMiddleground($url, $data);
        return $result;
    }

    /**
     * 验证账号是否正确
     * @param $account
     * @param $password
     * @return mixed|array
     */
    public static function checkTpsAccount($account, $password)
    {
        $data = array(
            'account' => $account,
            'password' => $password,
        );
        $url = config('tpsapi.check_url');
        $result = self::postTps($url, $data);
        return $result;

    }

    public static function getUserInfo($account)
    {
        $data = array(
            'account' => $account
        );
        $url = config('tpsapi.get_user_info');
        $result = self::postTps($url, $data);
        return $result;
    }

    /**
     * 创建账号
     * @param $account string 账号
     * @param $password string 密码
     * @param string $parentAccount 父级账号, 运营中心创建账号时为空
     * @param int $type 账号类型 1-运营中心账号  2- 商户创建
     * @return mixed|array
     */
    public static function createTpsAccount($account, $password, $parentAccount = '', $type = 1)
    {
        $data = array(
            'account' => $account,
            'password' => $password,
            'from' => $type,
            'p_account' => $parentAccount
        );
        $url = config('tpsapi.register_url');
        $result = self::postTps($url, $data);
        return $result;

    }

    /**
     * 消费记录对接
     * Author：Jerry
     * Date:180828
     * @param $data array 存储数据
     * @return mixed|string
     */
    public static function syncQuotaRecords($data )
    {
        $url = config('tpsapi.quota_url');
        $result = self::postMiddleground($url, $data);
        return $result;
    }

    /**
     * 中台接口post请求
     * @param $url
     * @param $data
     * @return mixed|array
     */
    public static function postMiddleground($url, $data)
    {
        $token = config('tpsapi.mail_token');
        $postData = json_encode($data);
        $headers = [
            'Content-type' => 'application/json;charset=utf-8',
            'Accept' => 'application/json',
            'token' => $token
        ];
        $client = new Client();
        $response = $client->post($url, [
            'body' => $postData,
            'headers' => $headers
        ]);
        $responseCode = $response->getStatusCode();
        $responseContent = $response->getBody()->getContents();
        Log::debug('请求中台接口:', compact('url', 'data', 'responseCode', 'responseContent'));
        if ($responseCode !== 200) {
            Log::error('请求中台接口失败', compact('url', 'data', 'responseCode', 'responseContent'));
            throw new BaseResponseException("网络请求失败");
        }
        $result = is_string($responseContent) ? json_decode($responseContent, 1) : $responseContent;
        return $result;
    }

    /**
     * TPS接口post请求
     * @param $url
     * @param $data
     * @return mixed|string
     */
    public static function postTps($url, $data)
    {
        $key = config('tpsapi.key');
        $encryData = TpsApi::apiEncrypt(json_encode($data), $key);
        $encryToken = TpsApi::apiEncrypt($key, $key);
        $postData = [
            'token' => $encryToken,
            'data' => $encryData,
        ];
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $postData
        ]);
        $responseCode = $response->getStatusCode();
        $responseContent = $response->getBody()->getContents();
        Log::debug('请求TPS接口:', compact('url', 'data', 'responseCode', 'responseContent'));
        if ($responseCode !== 200) {
            Log::error('请求TPS接口失败', compact('url', 'data', 'responseCode', 'responseContent'));
            throw new BaseResponseException("网络请求失败");
        }
        $array = is_string($responseContent) ? json_decode($responseContent, 1) : $responseContent;
        return $array;
    }

    /**
     * 加密函数(Tps接口需要用)
     * @param $string
     * @param $key
     * @return mixed
     */
    public static function apiEncrypt($string, $key = '')
    {

        $encryptKey = md5($key);
        $keyLen = strlen($encryptKey);
        $data = substr(md5($string . $encryptKey), 0, 8) . $string;
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