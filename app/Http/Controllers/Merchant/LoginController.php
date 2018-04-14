<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 14:58
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantAccount;
use App\Result;

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

        session([
            config('merchant.user_session') => $user,
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $this->getMenus(),
        ]);
    }

    private function getMenus()
    {
        return [
            [ 'id' => 1, 'name' => '商品管理', 'level' => 1, 'url' => '/merchant/goods',],
            [ 'id' => 2, 'name' => '订单管理', 'level' => 1, 'url' => '/merchant/orders',],
            [ 'id' => 3, 'name' => '财务管理', 'level' => 1, 'url' => '/merchant/settlements',],
        ];
    }

}