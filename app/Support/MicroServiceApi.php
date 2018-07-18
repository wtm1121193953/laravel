<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:06
 */

namespace App\Support;


use App\Exceptions\BaseResponseException;
use App\ResultCode;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MicroServiceApi
{
    const APP_KEY = 23401652;
    const SECRET_KEY = '5756af9812b528f72940af0f3ac74bb8';
    const SIGN_NAME = '微服务';

    public static function get($url, $data)
    {

        $client = new Client();
        $response = $client->post($url, [
            'query' => $data
        ]);
        if($response->getStatusCode() !== 200){
            Log::error('微服务网络请求失败', compact('url', 'data'));
            throw new BaseResponseException("网络请求失败");
        }
        $result = $response->getBody()->getContents();
        $array = is_string($result) ? json_decode($result, 1) : $result;
        return $array;
    }

    public static function post($url, $data)
    {
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => $data
        ]);
        if($response->getStatusCode() !== 200){
            Log::error('微服务网络请求失败', compact('url', 'data'));
            throw new BaseResponseException("网络请求失败");
        }
        $result = $response->getBody()->getContents();
        $array = is_string($result) ? json_decode($result,1 ) : $result;
        return $array;
    }

    /**
     * 发送模板消息
     * @param $mobile
     * @param $templateId
     * @param $params
     */
    public static function sendTemplateSms($mobile, $templateId, $params)
    {
        $url = 'http://msg.niucha.ren/api/sms/send/alidayu';

        $data = [
            'appKey' => self::APP_KEY,
            'secretKey' => self::SECRET_KEY,
            'to' => $mobile,
            'signName' => self::SIGN_NAME,
            'templateId' => $templateId,
            'params' => $params,
        ];
        $result = MicroServiceApi::post($url, $data);
        if($result['code'] !== 0){
            Log::error('短信发送失败', compact('url', 'data', 'result'));
            $message = '发送失败';
            $code = ResultCode::SMS_SEND_ERROR;
            if($result['code'] == 15){
                $message = '发送频率超限';
                $code = ResultCode::SMS_BUSINESS_LIMIT_CONTROL;
            }
            throw new BaseResponseException($message, $code);
        }
    }
}