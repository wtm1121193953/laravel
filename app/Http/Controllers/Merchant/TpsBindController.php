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
use App\Modules\Sms\SmsVerifyCodeService;
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

    /**
     * @throws \Exception
     */
    public function bindAccount()
    {
        $this->validate(request(), [
            'mobile' => 'required',
            'verifyCode' => 'required',
        ]);
        $mobile = request('mobile');
        $verifyCode = request('verifyCode');
        if ( !SmsVerifyCodeService::checkVerifyCode($mobile, $verifyCode) ){
            throw new ParamInvalidException('验证码错误');
        }
        $merchantId = request()->get('current_user')->merchant_id;
        $bindInfo = TpsBindService::bindTpsAccountForMerchant($merchantId, $mobile);
        return Result::success($bindInfo);
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
        $smsVerifyCode = SmsVerifyCodeService::add($mobile);
        $result = SmsVerifyCodeService::sendVerifyCode($smsVerifyCode->mobile, $smsVerifyCode->verify_code);

        if ($result['code'] == 0){
            return Result::success();
        }else{
            throw new BaseResponseException($result['message'], $result['code']);
        }
    }
}