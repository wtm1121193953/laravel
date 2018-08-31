<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/8/31
 * Time: 下午7:46
 */
namespace App\HTTP\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Result;
use App\ResultCode;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\Wallet;
//用户账单
use App\Modules\Wallet\WalletBill;

class WalletController extends Controller{
    /**
     * 获取用户钱包信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWallet()
    {
        $user = request()->get('current_user');

        $value = empty($user->id)?'':$user->id;

        //判断userId是否为空
        if (strlen($value) <= 0) {
            return Result::error(ResultCode::UNLOGIN, '用户未登录');
        }

        $wallet = WalletService::getWalletInfoByOriginInfo($value, Wallet::ORIGIN_TYPE_USER);
        return Result::success($wallet);
    }

    /**
     * 获取账单信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBills()
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $type = request('type');
        $user = request()->get('current_user');
        $bills = WalletService::getBillList([
            'originId' => $user->id,
            'originType' => WalletBill::ORIGIN_TYPE_USER,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'typeArr' => [$type],
        ], 20);

        return Result::success([
            'list' => $bills->items(),
            'total' => $bills->total(),
        ]);
    }
}