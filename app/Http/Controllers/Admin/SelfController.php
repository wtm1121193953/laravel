<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 12:47
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminService;
use App\Modules\Admin\AdminUser;
use App\Result;
use Illuminate\Support\Facades\Session;

class SelfController extends Controller
{

    protected $user = null;

    public function __construct()
    {
        $this->user = request()->get('current_user');
    }

    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $user = AdminUser::where('username', request('username'))->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(AdminUser::genPassword(request('password'), $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }

        $rules = AdminService::getRulesForUser($user);
        $menuTree = AdminService::convertRulesToTree($rules);

        session([
            config('admin.user_session') => $user,
            config('admin.user_rule_session') => $rules
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $menuTree
        ]);
    }

    public function logout()
    {
        Session::remove(config('admin.user_session'));
        Session::remove(config('admin.user_rule_session'));
        return Result::success();
    }

    public function getRules()
    {
        $user = request()->get('current_user');
        if(empty($user)){
            throw new UnloginException();
        }
        $rules = AdminService::getRulesForUser($user);
        $menuTree = AdminService::convertRulesToTree($rules);

        session([
            config('admin.user_rule_session') => $rules
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $menuTree
        ]);
    }

    public function modifyPassword()
    {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required',
            'reNewPassword' => 'required|same:newPassword'
        ]);
        $user = request()->get('current_user');
        // 检查原密码是否正确
        if(AdminUser::genPassword(request('password'), $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $salt = str_random();
        $user->salt = $salt;
        $user->password = AdminUser::genPassword(request('newPassword'), $salt);
        $user->save();
        return Result::success($user);
    }

}