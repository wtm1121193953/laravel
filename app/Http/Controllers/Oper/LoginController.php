<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 0:08
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\PasswordErrorException;
use App\Http\Controllers\Controller;
use App\Modules\Oper\OperAccount;
use App\Result;

class LoginController extends Controller
{

    /**
     * 登陆接口
     */
    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $user = OperAccount::where('account', request('username'))->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(OperAccount::genPassword(request('password'), $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }
        $user->username = $user->username ?? $user->account;

        session([
            config('oper.user_session') => $user,
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $this->getMenus(),
        ]);
    }

    private function getMenus()
    {
        return [
            [ 'id' => 1, 'name' => '商户管理', 'level' => 1, 'url' => '/oper/merchants',],
            [ 'id' => 2, 'name' => '财务管理', 'level' => 1, 'url' => '/oper/settlements',],
        ];
    }

}