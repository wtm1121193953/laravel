<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/3
 * Time: 下午3:59
 */
namespace App\Http\Controllers\UserApp;

use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletWithdrawService;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\BankCardService;
use App\Result;
use App\ResultCode;
use App\Support\Utils;

class WalletWithdrawController extends Controller
{

    /**
     * 提现操作
     * Author：  zwg
     * Date：    180903
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function withdraw()
    {
        $password = request('password');
        $password = Utils::aesDecrypt($password);
        if (!preg_match('/^\d{6}$/',$password)) {
            throw new ParamInvalidException('密码必须是数字');
        }
        $this->validate( request(), [
            'card_id'   =>  'required',
            'amount'    =>  'required|numeric|min:100'
        ],[
            'card_id.required'  =>  '银行卡信息有误',
            'amount.min'        =>  '最低提现金额为100元',
            'amount.required'   =>  '提现金额不可为空'
        ]);

        // 获取当前对象信息
        $obj   = request()->get('current_user');
        // 获取钱包信息
        $wallet = WalletService::getWalletInfo( $obj );
        // 获取银行卡信息
        $card   = BankCardService::getCardById( request()->input('card_id'));
        $isOk   = WalletWithdrawService::checkWithdrawPasswordByOriginInfo( $password, $obj->id, $wallet->origin_type);
        if( !$isOk )
        {
            throw new NoPermissionException('提现密码错误');
        }
        if( !$card )
        {
            throw new BaseResponseException('无银行卡信息', ResultCode::DB_QUERY_FAIL);
        }
        // 判断当前是否可提现
//        $days = WalletWithdrawService::getWithdrawableDays();
//        if(!in_array(date('d'), $days)){
//            throw new BaseResponseException('当前日期不可提现');
//        }

        // 注入银行卡信息
        $obj->bank_card_type        = $card->bank_card_type;
        $obj->bank_open_name        = $card->bank_card_open_name;
        $obj->bank_card_no          = $card->bank_card_no;
        $obj->sub_bank_name         = $card->bank_name;
        WalletWithdrawService::createWalletWithdrawAndUpdateWallet( $wallet, $obj, request()->input('amount'), []);
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
            'withdrawRatio' => UserCreditSettingService::getUserWithdrawChargeRatio(),
            'minAmount' => 100,
            'isSetWithdrawPassword' => empty($wallet->withdraw_password) ? 0 : 1,
            'hasBankCard' => $cards->count() <= 0 ? 0 : 1,
            'balance' => $wallet->balance,
            // 判断现今可否结算
            'isWithdraw' => in_array(date('d'), WalletWithdrawService::getWithdrawableDays()),
//             'isWithdraw' => '1',
            //显示可提现日期
            'days'    =>  WalletWithdrawService::getWithdrawableDays(),
        ]);
    }
}