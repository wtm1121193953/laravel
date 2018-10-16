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
    const ALIYUN_APP_KEY = 23401652;
    const ALIYUN_SECRET_KEY = '5756af9812b528f72940af0f3ac74bb8';
    const SIGN_NAME = '大千生活';

    /**
     * 团购商品购买成功通知模板ID
     * 模板内容:
     * 订单号${orderNo}：${name}已下单成功，份数：${number}，使用截止日期：${endDate}，请凭券码${verifyCode}到商家进行消费，感谢您的使用。
     */
    const GROUP_BUY_TEMPLATE_ID = 'SMS_139985440';
    /**
     * 单品商品购买成功通知模板ID
     * 模板内容:
     * 订单号${orderNo}：${name}等${number}份商品已下单成功，请及时到商家进行消费，感谢您的使用。
     */
    const DISHES_TEMPLATE_ID = 'SMS_139975636';

    /**
     * 短信验证码接口 APP_KEY
     */
    const APP_KEY = '50e7a5f180839466cceae9604e422e13';

    public static function get($url, $data)
    {

        $client = new Client();
        $response = $client->get($url, [
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
            'appKey' => self::ALIYUN_APP_KEY,
            'secretKey' => self::ALIYUN_SECRET_KEY,
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
            Log::error($message, ['code' => $code]);
        }
    }

    public static function sendVerifyCodeV2($to, $content)
    {
        $url = 'http://msg.niucha.ren/api/v2/sms/verifyCode';
        $data = [
            'appKey' => self::APP_KEY,
            'to' => $to,
            'content' => $content,
            'signName' => self::SIGN_NAME,
        ];
        $result = self::post($url, $data);
        if($result['code'] !== 0){
            Log::error('短信发送失败', compact('url', 'data', 'result'));
            $message = $result['message'] ?? '发送失败';
            $code = ResultCode::SMS_SEND_ERROR;
            throw new BaseResponseException($message, $code);
        }
        return $result;
    }

    public static function sendNotifyV2($to, $content)
    {
        $url = 'http://msg.niucha.ren/api/v2/sms/notify';
        $data = [
            'appKey' => self::APP_KEY,
            'to' => $to,
            'content' => $content,
            'signName' => self::SIGN_NAME,
        ];
        $result = self::post($url, $data);
        if($result['code'] !== 0){
            Log::error('短信发送失败', compact('url', 'data', 'result'));
            $message = $result['message'] ?? '发送失败';
            $code = ResultCode::SMS_SEND_ERROR;
            throw new BaseResponseException($message, $code);
        }
        return $result;
    }
}