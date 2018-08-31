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
}