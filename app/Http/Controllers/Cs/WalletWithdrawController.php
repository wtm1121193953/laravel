<?php

namespace App\Http\Controllers\Cs;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Sms\SmsService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;

class WalletWithdrawController extends Controller
{

    /**
     * 获取钱包密码是否设置 和 商户电话号码
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletPasswordInfo()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = CsMerchantService::getById($merchantId);

        $wallet = WalletService::getWalletInfo($merchant);
        $editPassword = $wallet->withdraw_password != '';

        return Result::success([
            'editPassword' => $editPassword,
            'mobile' => $merchant->contacter_phone,
        ]);
    }

    /**
     * 设置更新商户取现密码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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
            $merchant = CsMerchantService::getById($merchantId);

            $wallet = WalletService::getWalletInfo($merchant);
            WalletService::updateWalletWithdrawPassword($wallet, $password);

            return Result::success();
        }else {
            throw new BaseResponseException('验证码错误');
        }
    }

    /**
     * 获取提现表单所需的相关信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWithdrawInfoAndBankInfo()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = CsMerchantService::getById($merchantId);
        $wallet = WalletService::getWalletInfo($merchant);
        $ratio = UserCreditSettingService::getMerchantWithdrawChargeRatioByBankCardType($merchant->bank_card_type);

        return Result::success([
            'balance' => $wallet->balance,  // 可提现金额
            'bankOpenName' => $merchant->bank_open_name,
            'bankCardNo' => $merchant->bank_card_no,
            'subBankName' => $merchant->sub_bank_name,
            'bankCardType' => $merchant->bank_card_type, // 账户类型 1-公司 2-个人
            'ratio' => $ratio,  // 手续费百分比
            'isSetPassword' => $wallet->withdraw_password != '',
        ]);
    }

    /**
     * 商户提现操作
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function withdrawApplication()
    {
        $this->validate(request(), [
            'amount' => 'required',
            'withdrawPassword' => 'required|max:6',
        ]);

        $amount = request('amount');
        $withdrawPassword = request('withdrawPassword');
        $invoiceExpressCompany = request('invoiceExpressCompany', '');
        $invoiceExpressNo = request('invoiceExpressNo', '');

        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = CsMerchantService::getById($merchantId);
        if ($merchant->status != CsMerchant::STATUS_ON || $merchant->audit_status != CsMerchant::AUDIT_STATUS_SUCCESS) {
            throw new BaseResponseException('商户状态异常，请联系客服');
        }

        $wallet = WalletService::getWalletInfo($merchant);
        $checkPass = WalletWithdrawService::checkWithdrawPasswordByOriginInfo($withdrawPassword, $merchantId, Wallet::ORIGIN_TYPE_CS);

        if ($checkPass) {
            $param = compact('invoiceExpressCompany', 'invoiceExpressNo');
            $walletWithdraw = WalletWithdrawService::createWalletWithdrawAndUpdateWallet($wallet, $merchant, $amount, $param);

            return Result::success($walletWithdraw);
        } else {
            throw new BaseResponseException('提现密码错误');
        }
    }

    /**
     * 获取商户的提现明细
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWithdrawDetail()
    {
        $this->validate(request(), [
            'id' => 'required|min:1',
        ]);

        $id = request('id');
        $withdraw = WalletWithdrawService::getWalletWithdrawById($id);

        return Result::success($withdraw);
    }

    /**
     * 获取商户开票规则doc路径 doc下载
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getInvoiceTemplatePath()
    {
        $path = storage_path('app/help-doc/merchant_invoice_template.doc');
        return Result::success(['url' => $path]);
    }
}