<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 12:47
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminRuleService;
use App\Modules\Admin\AdminUserService;
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

        $user = AdminUserService::checkPasswordByUsername(request('username'), request('password'));

        $rules = AdminUserService::getUserRules($user);
        $menuTree = AdminRuleService::convertRulesToTree($rules);

        session([
            config('admin.user_session') => $user,
            config('admin.user_rule_session') => $rules
        ]);
        return Result::success([
            'user' => $user,
            'menus' => $menuTree,
            'rules' => $rules,
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

        $rules = AdminUserService::getUserRules($user);
        $menuTree = AdminRuleService::convertRulesToTree($rules);

        session([
            config('admin.user_rule_session') => $rules
        ]);

        return Result::success([
            'user' => $user,
            'menus' => $menuTree,
            'rules' => $rules,
        ]);
    }

    public function modifyPassword()
    {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required|between:6,30',
            'reNewPassword' => 'required|same:newPassword'
        ]);
        $user = request()->get('current_user');

        $user = AdminUserService::modifyPassword($user, request('password'), request('newPassword'));

        // 修改密码成功后更新session中的user
        session([
            config('admin.user_session') => $user,
        ]);

        return Result::success($user);
    }

}