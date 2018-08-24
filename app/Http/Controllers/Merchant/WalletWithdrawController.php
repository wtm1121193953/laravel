<?php

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Sms\SmsService;
use App\Modules\Wallet\WalletService;
use App\Result;

class WalletWithdrawController extends Controller
{
    /**
     * 获取钱包密码是否设置 和 商户电话号码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWalletPasswordInfo()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = MerchantService::getById($merchantId);

        $wallet = WalletService::getWalletInfo($merchant);
        $editPassword = $wallet->withdraw_password != '';

        return Result::success([
            'editPassword' => $editPassword,
            'mobile' => $merchant->contacter_phone,
        ]);
    }

    public function setWalletPassword()
    {
        $this->validate(request(), [
            'mobile' => 'required',
            'verifyCode' => 'required|max:6',
            'password' => 'required|max:6',
            'checkPassword' => 'required|max:6|same:password'
        ]);

        $mobile = request('mobile');
        $verifyCode = request('verifyCode');
        $password = request('password');

        if (SmsService::checkVerifyCode($mobile, $verifyCode)){
            $merchantId = request()->get('current_user')->merchant_id;
            $merchant = MerchantService::getById($merchantId);

            $wallet = WalletService::getWalletInfo($merchant);
            WalletService::updateWalletWithdrawPassword($wallet, $password);

            return Result::success();
        }else {
            throw new BaseResponseException('验证码错误');
        }
    }
}