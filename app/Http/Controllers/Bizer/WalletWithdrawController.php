<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Bizer\Bizer;
use App\Modules\Bizer\BizerIdentityAuditRecord;
use App\Modules\Bizer\BizerService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\BankCard;
use App\Modules\Wallet\BankCardService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;
use Illuminate\Support\Facades\DB;

class WalletWithdrawController extends Controller
{
    /**
     * 业务员提现设置
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function withdrawSetting()
    {
        $this->validate(request(), [
            'bank_card_no' => 'required|numeric|min:10000000|max:99999999999999999999999999999999999|unique:bank_cards',
            'bank_card_open_name' => 'required|max:20',
            'bank_name' => 'required|max:20',
            'password' => 'required|max:6',
            'checkPassword' => 'required|max:6|same:password'
        ]);

        $bankCardNo = request('bank_card_no');
        $bankCardOpenName = request('bank_card_open_name');
        $bankName = request('bank_name');

        $password = request('password');

        $bizer = request()->get('current_user');

        DB::beginTransaction();
        try {
            // 1.添加银行卡信息
            $saveData = [
                'bank_card_no' => $bankCardNo,
                'bank_card_open_name' => $bankCardOpenName,
                'bank_name' => $bankName,
            ];
            BankCardService::addCard($saveData, $bizer, BankCard::ORIGIN_TYPE_BIZER);

            // 2.设置提现密码
            $wallet = WalletService::getWalletInfo($bizer);
            WalletService::updateWalletWithdrawPassword($wallet, $password);

            DB::commit();

            return Result::success();
        } catch (\Exception $e) {
            DB::rollBack();

            $msg = $e->getResponse()->original['message'] ?: '提现设置失败';
            throw new BaseResponseException($msg);
        }
    }

    /**
     * 获取业务员 审核记录 和 钱包信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBizerIdentityAuditRecordAndWalletInfo()
    {
        $bizer = request()->get('current_user');
        $identityAuditRecord = BizerService::getBizerIdentityAuditRecordByBizerId($bizer->id);

        $wallet = WalletService::getWalletInfo($bizer);
        if ($wallet && $wallet->withdraw_password) {
            $isSetPassword = true;
        } else {
            $isSetPassword = false;
        }

        return Result::success([
            'record' => $identityAuditRecord,
            'isSetPassword' => $isSetPassword,
        ]);
    }

    /**
     * 添加业务员审核记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBizerIdentityAuditRecord()
    {
        $this->validate(request(), [
            'name' => 'required|max:20',
            'id_card_no' => 'required|identitycards|unique:bizer_identity_audit_records',
            'front_pic' => 'required',
            'opposite_pic' => 'required',
        ]);

        $name = request('name');
        $idCardNo = request('id_card_no');
        $frontPic = request('front_pic');
        $oppositePic = request('opposite_pic');

        $bizer = request()->get('current_user');

        $bizerIdentityAuditRecord = BizerService::addBizerIdentityAuditRecord(compact('name', 'idCardNo', 'frontPic', 'oppositePic'), $bizer);

        return Result::success($bizerIdentityAuditRecord);
    }

    /**
     * 编辑业务员审核记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function editBizerIdentityAuditRecord()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required|max:20',
            'id_card_no' => 'required|identitycards',
            'front_pic' => 'required',
            'opposite_pic' => 'required',
        ]);

        $id = request('id');
        $name = request('name');
        $idCardNo = request('id_card_no');
        $frontPic = request('front_pic');
        $oppositePic = request('opposite_pic');

        $bizerIdentityAuditRecord = BizerService::getBizerIdentityAuditRecordById($id);
        if (empty($bizerIdentityAuditRecord)) {
            throw new BaseResponseException('该业务员审核信息不存在');
        }

        $bizerIdentityAuditRecord = BizerService::editBizerIdentityAuditRecord(compact('name', 'idCardNo', 'frontPic', 'oppositePic'), $bizerIdentityAuditRecord);

        return Result::success($bizerIdentityAuditRecord);
    }

    /**
     * 获取业务员身份和银行卡信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankCardAndIdCardInfo()
    {
        $bizer = request()->get('current_user');
        $identityAuditRecord = BizerService::getBizerIdentityAuditRecordByBizerId($bizer->id);
        if (empty($identityAuditRecord)) throw new BaseResponseException('业务员身份信息不存在');

        $bankCard = BankCardService::getBankCardByOriginInfo($bizer->id, BankCard::ORIGIN_TYPE_BIZER);
        if (empty($bankCard)) throw new BaseResponseException('业务员银行卡信息不存在');

        return Result::success([
            'identityAuditRecord' => $identityAuditRecord,
            'bankCard' => $bankCard,
        ]);
    }

    /**
     * 获取提现表单所需的相关信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdrawInfoAndBankInfo()
    {
        $bizerId = request()->get('current_user')->id;
        $bizer = BizerService::getById($bizerId);
        $wallet = WalletService::getWalletInfo($bizer);
        $bankCard = BankCardService::getBankCardByOriginInfo($bizerId, BankCard::ORIGIN_TYPE_BIZER);
        $ratio = UserCreditSettingService::getBizerWithdrawChargeRatio();
        if (empty($bankCard)) {
            throw new BaseResponseException('提现银行卡不存在');
        }

        return Result::success([
            'balance' => $wallet->balance,  // 可提现金额
            'bankCardOpenName' => $bankCard->bank_card_open_name,
            'bankCardNo' => $bankCard->bank_card_no,
            'bankName' => $bankCard->bank_name,
            'ratio' => $ratio,  // 手续费百分比
            'bizerMobile' => $bizer->mobile,
            'isSetPassword' => $wallet->withdraw_password != '',
        ]);
    }

    /**
     * 业务员提现操作
     * @return \Illuminate\Http\JsonResponse
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

        $bizerId = request()->get('current_user')->id;
        $bizer = BizerService::getById($bizerId);
        $bizerIdentityAuditRecord = BizerService::getBizerIdentityAuditRecordByBizerId($bizerId);
        if ($bizer->status != Bizer::STATUS_ON || $bizerIdentityAuditRecord->status != BizerIdentityAuditRecord::STATUS_AUDIT_SUCCESS) {
            throw new BaseResponseException('商户状态异常，请联系客服');
        }

        $wallet = WalletService::getWalletInfo($bizer);
        $checkPass = WalletWithdrawService::checkWithdrawPasswordByOriginInfo($withdrawPassword, $bizerId, Wallet::ORIGIN_TYPE_BIZER);

        if ($checkPass) {
            $walletWithdraw = WalletWithdrawService::createWalletWithdrawAndUpdateWallet($wallet, $bizer, $amount);

            return Result::success($walletWithdraw);
        } else {
            throw new BaseResponseException('提现密码错误');
        }
    }

    /**
     * 忘记密码 重置提现密码
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetWithdrawPassword()
    {
        $this->validate(request(), [
            'password' => 'required|max:6',
            'checkPassword' => 'required|max:6|same:password'
        ]);

        $password = request('password');

        $bizerId = request()->get('current_user')->id;
        $bizer = BizerService::getById($bizerId);
        if (empty($bizer)) {
            throw new BaseResponseException('该业务员不存');
        }

        $wallet = WalletService::getWalletInfo($bizer);
        WalletService::updateWalletWithdrawPassword($wallet, $password);

        return Result::success();
    }
}