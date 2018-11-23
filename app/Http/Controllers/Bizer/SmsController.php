<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Sms\SmsVerifyCodeService;
use App\Result;

class SmsController extends Controller
{

    public function sendVerifyCode()
    {
        $this->validate(request(), [
            'mobile' => 'required|regex:/^1[3456789]\d{9}/'
        ]);
        $mobile = request('mobile');

        $smsVerifyCode = SmsVerifyCodeService::add($mobile);
        $result = SmsVerifyCodeService::sendVerifyCode($smsVerifyCode->mobile, $smsVerifyCode->verify_code);

        if ($result['code'] == 0){
            return Result::success();
        }else{
            throw new BaseResponseException($result['message'], $result['code']);
        }
    }

    /**
     * 校验验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVerifyCode()
    {
        $this->validate(request(), [
            'mobile' => 'required|regex:/^1[3456789]\d{9}/',
            'verifyCode' => 'required',
        ]);
        $mobile = request('mobile');
        $verifyCode = request('verifyCode');

        $res = SmsVerifyCodeService::checkVerifyCode($mobile, $verifyCode);

        if ($res) {
            return Result::success();
        } else {
            throw new BaseResponseException('验证码错误');
        }
    }
}