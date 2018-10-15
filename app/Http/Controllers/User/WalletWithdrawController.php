<?php

namespace App\Http\Controllers\User;

use App\Exceptions\BaseResponseException;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletWithdrawService;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\BankCardService;
use App\Result;
use App\ResultCode;

class WalletWithdrawController extends Controller
{

    /**
     * 提现操作
     * Author：  Jerry
     * Date：    180901
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function withdraw( Request $request )
    {
        $this->validate( $request, [
            'card_id'   =>  'required',
            'amount'    =>  'required|numeric|min:100',
            'password'  =>  'required'
        ],[
            'card_id.required'  =>  '银行卡信息有误',
            'amount.min'        =>  '最低提现金额为100',
            'amount.required'   =>  '提现金额不可为空'
        ]);
        // 获取当前对象信息
        $obj   = $request->get('current_user');
        // 获取钱包信息
        $wallet = WalletService::getWalletInfo( $obj );
        // 获取银行卡信息
        $card   = BankCardService::getCardById( $request->input('card_id'));
        $isOk   = WalletWithdrawService::checkWithdrawPasswordByOriginInfo( $request->input('password'), $obj->id, $wallet->origin_type);
        if( !$isOk )
        {
            return Result::error(ResultCode::NO_PERMISSION, '提现密码错误');
        }
        if( !$card )
        {
            return Result::error(ResultCode::DB_QUERY_FAIL, '无银行卡信息');
        }
        // 判断当前是否可提现
        $days = WalletWithdrawService::getWithdrawableDays();
        if(!in_array(date('d'), $days)){
            throw new BaseResponseException('当前日期不可提现');
        }

        // 注入银行卡信息
        $obj->bank_card_type        = $card->bank_card_type;
        $obj->bank_open_name        = $card->bank_card_open_name;
        $obj->bank_card_no          = $card->bank_card_no;
        $obj->sub_bank_name         = $card->bank_name;
        WalletWithdrawService::createWalletWithdrawAndUpdateWallet( $wallet, $obj, $request->input('amount'), []);
        return Result::success('提现成功');
    }

    /**
     * 获取提现配置, 提现费率以及最低提现金额
     */
    public function getWithdrawConfig()
    {
        $user = request()->get('current_user');
        $cards = BankCardService::getList( $user );
        // 是否有提款密码
        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);
        // 获取用户可提现金额

        return Result::success([
            'withdrawRatio' => UserCreditSettingService::getUserWithdrawChargeRatio() / 100,
            'minAmount' => 100,
            'isSetWithdrawPassword' => empty($wallet->withdraw_password) ? 0 : 1,
            'hasBankCard' => $cards->count() <= 0 ? 0 : 1,
            'balance' => $wallet->balance,
            // 判断现今可否结算
            'isWithdraw' => in_array(date('d'), WalletWithdrawService::getWithdrawableDays()),
            //显示可提现日期
            'days' => WalletWithdrawService::getWithdrawableDays(),
        ]);
    }
}
