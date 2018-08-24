<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditSettingService;

/**
 * 提现相关Service
 * Class WalletWithdrawService
 * @package App\Modules\Wallet
 */
class WalletWithdrawService extends BaseService
{

    /**
     * 根据id获取提现记录
     * @param $id
     * @param array $fields
     * @return WalletWithdraw
     */
    public static function getWalletWithdrawById($id, $fields = ['*'])
    {
        $walletWithdraw = WalletWithdraw::find($id, $fields);

        return $walletWithdraw;
    }

    /**
     * 生成 钱包提现流水单号
     * @return string
     */
    public static function createWalletWithdrawNo()
    {
        $billNo = date('Ymd') .substr(time(), -7, 7). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $billNo;
    }

    /**
     * 校验提现密码
     * @param $withdrawPassword
     * @param $originId
     * @param $originType
     * @return bool
     */
    public static function checkWithdrawPasswordByOriginInfo($withdrawPassword, $originId, $originType)
    {
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);
        if (!$wallet->withdraw_password) {
            throw new BaseResponseException('请设置提现密码');
        }
        $checkPass = Wallet::genPassword($withdrawPassword, $wallet->salt);
        if ($wallet->withdraw_password == $checkPass) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 创建提现记录 并更新钱包可提现余额
     * @param Wallet $wallet
     * @param Merchant|Oper|User $obj
     * @param $amount
     * @param $param
     * @return WalletWithdraw
     */
    public static function createWalletWithdrawAndUpdateWallet(Wallet $wallet, $obj, $amount, $param)
    {
        $invoiceExpressCompany = array_get($param, 'invoiceExpressCompany', '');
        $invoiceExpressNo = array_get($param, 'invoiceExpressNo', '');

        if ($obj instanceof User) {
            $originType = WalletWithdraw::ORIGIN_TYPE_USER;
            throw new BaseResponseException('暂不支持提现');
        } elseif ($obj instanceof Merchant) {
            $originType = WalletWithdraw::ORIGIN_TYPE_MERCHANT;
            $ratio = UserCreditSettingService::getMerchantWithdrawChargeRatioByBankCardType($obj->bank_card_type);
        } elseif ($obj instanceof Oper) {
            $originType = WalletWithdraw::ORIGIN_TYPE_OPER;
            $ratio = UserCreditSettingService::getOperWithdrawChargeRatio();
            $obj->bank_card_type = 1;
        } else {
            throw new BaseResponseException('用户类型错误');
        }

        // 1.创建提现记录
        $withdraw = new WalletWithdraw();
        $withdraw->wallet_id = $wallet->id;
        $withdraw->origin_id = $obj->id;
        $withdraw->origin_type = $originType;
        $withdraw->withdraw_no = self::createWalletWithdrawNo();
        $withdraw->amount = $amount;
        $withdraw->charge_amount = number_format($amount * $ratio / 100, 2);
        $withdraw->remit_amount = number_format($amount - number_format($amount * $ratio / 100, 2), 2);
        $withdraw->status = WalletWithdraw::STATUS_WITHDRAWING;
        $withdraw->invoice_express_company = $invoiceExpressCompany;
        $withdraw->invoice_express_no = $invoiceExpressNo;
        $withdraw->bank_card_type = $obj->bank_card_type;
        $withdraw->bank_card_open_name = $obj->bank_open_name;
        $withdraw->bank_card_no = $obj->bank_card_no;
        $withdraw->bank_name = $obj->sub_bank_name;
        $withdraw->save();

        // 2.更新钱包余额
        $wallet->balance = number_format($wallet->balance - $amount, 2);
        $wallet->save();

        return $withdraw;
    }
}