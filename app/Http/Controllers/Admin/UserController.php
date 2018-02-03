<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/1
 * Time: 17:31
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Exceptions\PasswordErrorException;
use App\Modules\Admin\AdminService;
use App\Modules\Admin\AdminUser;
use App\Result;
use App\ResultCode;

class UserController
{

    public function login()
    {
        $username = request('username');
        $password = request('password');
        if(empty($username) || empty($password)){
            throw new ParamInvalidException();
        }

        $user = AdminUser::where('username', $username)->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(AdminUser::genPassword($password, $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }

        $rules = AdminService::getRulesForUser($user);
        $menuTree = AdminService::convertRulesToTree($rules);

        session(['admin_user' => $user]);
        session(['admin_user_rules' => $rules]);

        return Result::success([
            'userInfo' => $user,
            'menus' => $menuTree
        ]);

    }
}