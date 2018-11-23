<?php

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Sms\SmsVerifyCodeService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;

class WalletWithdrawController extends Controller
{
    /**
     * 获取钱包密码是否设置 和 运营中心电话号码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWalletPasswordInfo()
    {
        $operId = request()->get('current_user')->oper_id;
        $oper = OperService::getById($operId);

        $wallet = WalletService::getWalletInfo($oper);
        $editPassword = $wallet->withdraw_password != '';

        return Result::success([
            'editPassword' => $editPassword,
            'mobile' => $oper->tel,
        ]);
    }

    /**
     * 设置更新运营中心取现密码
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

        if (SmsVerifyCodeService::checkVerifyCode($mobile, $verifyCode)){
            $operId = request()->get('current_user')->oper_id;
            $oper = OperService::getById($operId);

            $wallet = WalletService::getWalletInfo($oper);
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
        $operId = request()->get('current_user')->oper_id;
        $oper = OperService::getById($operId);
        $wallet = WalletService::getWalletInfo($oper);
        $ratio = UserCreditSettingService::getOperWithdrawChargeRatio();

        return Result::success([
            'balance' => $wallet->balance,  // 可提现金额
            'bankOpenName' => $oper->bank_open_name,
            'bankCardNo' => $oper->bank_card_no,
            'subBankName' => $oper->sub_bank_name,
            'ratio' => $ratio,  // 手续费百分比
            'isSetPassword' => $wallet->withdraw_password != '',
        ]);
    }

    /**
     * 运营中心提现操作
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

        $operId = request()->get('current_user')->oper_id;
        $oper = OperService::getById($operId);
        $wallet = WalletService::getWalletInfo($oper);

        if ($oper->status != Oper::STATUS_NORMAL) {
            throw new BaseResponseException('运营中心状态异常，请联系客服');
        }

        $checkPass = WalletWithdrawService::checkWithdrawPasswordByOriginInfo($withdrawPassword, $operId, Wallet::ORIGIN_TYPE_OPER);

        if ($checkPass) {
            $param = compact('invoiceExpressCompany', 'invoiceExpressNo');
            $walletWithdraw = WalletWithdrawService::createWalletWithdrawAndUpdateWallet($wallet, $oper, $amount, $param);

            return Result::success($walletWithdraw);
        } else {
            throw new BaseResponseException('提现密码错误');
        }
    }

    /**
     * 获取运营中心的提现明细
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
        $path = storage_path('app/help-doc/oper_invoice_template.doc');
        return Result::success(['url' => $path]);
    }
}