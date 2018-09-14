<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Sms\SmsService;
use App\Result;

class SmsController extends Controller
{

    public function sendVerifyCode()
    {
        $this->validate(request(), [
            'mobile' => 'required|regex:/^1[3,4,5,6,7,8,9]\d{9}/'
        ]);
        $mobile = request('mobile');

        $smsVerifyCode = SmsService::add($mobile);
        $result = SmsService::sendVerifyCode($smsVerifyCode->mobile, $smsVerifyCode->verify_code);

        if ($result['code'] == 0){
            return Result::success();
        }else{
            throw new BaseResponseException($result['message'], $result['code']);
        }
    }
}