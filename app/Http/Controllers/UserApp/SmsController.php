<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 13:11
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Sms\SmsVerifyCode;
use App\Result;
use App\Support\MicroServiceApi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{

    public function sendVerifyCode()
    {
        $this->validate(request(), [
            'mobile' => 'required|size:11'
        ]);
        $mobile = request('mobile');
        if(!preg_match('/^1[3,4,5,6,7,8]\d{9}/', $mobile)){
            throw new ParamInvalidException('手机号码不合法');
        }
        $verifyCode = rand(1000, 9999);
        $record = new SmsVerifyCode();
        $record->mobile = $mobile;
        $record->verify_code = $verifyCode;
        $record->type = 1;
        $record->expire_time = Carbon::now()->addMinutes(10);
        $record->save();

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
            if($result['code'] == 15){
                $message .= ':发送频率超过限制';
            }
            throw new BaseResponseException($message);
        }

        return Result::success([
            'verify_code' => $verifyCode,
        ]);
    }
}