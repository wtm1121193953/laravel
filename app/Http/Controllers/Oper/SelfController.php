<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 0:08
 */

namespace App\Http\Controllers\Oper;

use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Oper\OperAccountService;
use App\Result;

class SelfController extends Controller
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

        $username = request('username');
        $password = request('password');

        $user = OperAccountService::login($username,$password);

        $menus = OperAccountService::getMenus($user->oper_id);

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    public function getMenus()
    {
        $user = request()->get('current_user');
        if(empty($user)){
            throw new UnloginException();
        }

        $menus = OperAccountService::getMenus($user->oper_id);

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    public function logout()
    {
        OperAccountService::logout();
        return Result::success();
    }

    public function modifyPassword()
    {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required|between:6,30',
            'reNewPassword' => 'required|same:newPassword'
        ]);
        $user = request()->get('current_user');
        $password = request('password');
        $newPassword = request('newPassword');

        $user = OperAccountService::modifyPassword($user,$password,$newPassword);

        return Result::success($user);
    }


}