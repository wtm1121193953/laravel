<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 14:58
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\PasswordErrorException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Result;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $user = MerchantAccount::where('account', request('username'))->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(MerchantAccount::genPassword(request('password'), $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }
        if($user->status != 1){
            throw new NoPermissionException('账号已被禁用');
        }
        $merchant = Merchant::findOrFail($user->merchant_id);
        if($merchant->status != 1){
            throw new NoPermissionException('商户已被冻结');
        }

        $user->username = $merchant->name;

        session([
            config('merchant.user_session') => $user,
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $this->getMenus(),
        ]);
    }

    public function logout()
    {
        Session::forget(config('merchant.user_session'));
        return Result::success();
    }

    private function getMenus()
    {
        return [
            [ 'id' => 1, 'name' => '商品管理', 'level' => 1, 'url' => '/merchant/goods',],
            [ 'id' => 2, 'name' => '订单管理', 'level' => 1, 'url' => '/merchant/orders',],

            [ 'id' => 3, 'name' => '人员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                [
                    [ 'id' => 4, 'name' => '我的会员', 'level' => 2, 'url' => '/merchant/invite/statistics/daily', 'pid' => 4,],
                ]
            ],
            [ 'id' => 5, 'name' => '财务管理', 'level' => 1, 'url' => '/merchant/settlements',],
            [ 'id' => 6, 'name' => '素材中心', 'level' => 1, 'url' => 'material', 'sub' =>
                [
                    [ 'id' => 7, 'name' => '分享会员二维码', 'level' => 2, 'url' => '/merchant/invite/channel', 'pid' => 8,],
                ]
            ],
        ];
    }

}