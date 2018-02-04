<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/1
 * Time: 17:31
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminService;
use App\Modules\Admin\AdminUser;
use App\Result;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{

    public function login()
    {
        $username = request('username');
        $password = request('password');
        if(empty($username) || empty($password)){
            throw new ParamInvalidException();
        }

        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $user = AdminUser::where('username', $username)->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(AdminUser::genPassword($password, $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }

        $rules = AdminService::getRulesForUser($user);
        $menuTree = AdminService::convertRulesToTree($rules);

        session([
            'admin_user' => $user,
            'admin_user_rules' => $rules
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $menuTree
        ]);
    }

    public function logout()
    {
        Session::remove('admin_user');
        Session::remove('admin_user_rules');
        return Result::success();
    }

    public function getRules()
    {
        $user = Session::get('admin_user');
        if(empty($user)){
            throw new UnloginException();
        }
        $rules = AdminService::getRulesForUser($user);
        $menuTree = AdminService::convertRulesToTree($rules);

        return Result::success([
            'user' => $user,
            'menus' => $menuTree
        ]);
    }
}