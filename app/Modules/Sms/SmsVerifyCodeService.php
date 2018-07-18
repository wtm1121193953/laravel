<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 15:29
 */

namespace App\Modules\Sms;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
use App\Support\MicroServiceApi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SmsVerifyCodeService extends BaseService
{
    /**
     * @param $mobile
     * @return SmsVerifyCode
     */
    public static function add($mobile)
    {
        $verifyCode = rand(1000, 9999);
        $record = new SmsVerifyCode();
        $record->mobile = $mobile;
        $record->verify_code = $verifyCode;
        $record->type = 1;
        $record->expire_time = Carbon::now()->addMinutes(10);
        $record->save();
        return $record;
    }

    /**
     * @param $mobile
     * @param $verifyCode
     */
    public static function sendVerifyCode($mobile, $verifyCode)
    {
        // 发送短信
        $url = 'http://msg.niucha.ren/api/sms/verifyCode';
        $data = [
            'to' => $mobile,
            'verifyCode' => $verifyCode,
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