<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/13
 * Time: 15:52
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Sms\SmsService;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use App\Result;

class TpsBindController extends Controller
{

    public function getBindInfo()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($merchantId, TpsBind::ORIGIN_TYPE_MERCHANT);
        return Result::success($bindInfo);
    }

    public function bindAccount()
    {
        
    }

    public function sendVerifyCode()
    {
        $this->validate(request(), [
            'mobile' => 'required',
        ]);
        $mobile = request('mobile');
        $bindInfo = TpsBindService::getTpsBindInfoByTpsAccount($mobile);
        if(!empty($bindInfo)){
            throw new ParamInvalidException('该手机号已被绑定, 请更换其他手机号');
        }
        $smsVerifyCode = SmsService::add($mobile);
        $result = SmsService::sendVerifyCode($smsVerifyCode->mobile, $smsVerifyCode->verify_code);

        if ($result['code'] == 0){
            return Result::success();
        }else{
            throw new BaseResponseException($result['message'], $result['code']);
        }
    }
}