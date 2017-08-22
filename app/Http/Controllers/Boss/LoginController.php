<?php
/**
 * User: Evan Lee
 * Date: 2017/8/16
 * Time: 18:17
 */

namespace App\Http\Controllers\Boss;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\BossAuth\BossAuthService;
use App\Result;
use Illuminate\Contracts\Session\Session;

class LoginController
{

    public function test()
    {
    }

    public function login()
    {
        $username = trim(request('username', ''));
        $password = trim(request('password', ''));
        $verifyCode = trim(request('verifyCode', ''));
        if(empty($username) || empty($password)){
            throw new ParamInvalidException('用户名及密码不能为空');
        }
        if(empty($verifyCode)){
            throw new ParamInvalidException('验证码不能为空');
        }

        if(!captcha_check($verifyCode)){
            throw new BaseResponseException('验证码错误');
        }

        $user = BossAuthService::getUserByName($username);
        if(empty($user)){
            throw new BaseResponseException('用户名或密码错误');
        }

        $password = BossAuthService::genPassword($password, $user->salt);
        if($password != $user->password){
            throw new BaseResponseException('用户名或密码错误');
        }

        // 获取用户权限列表
        $rules = BossAuthService::getEnableRulesByUserId($user->id);
        $tree = BossAuthService::convertRulesToTree($rules);

        session(['boss_user' => $user]);
        session(['boss_user_rules' => $rules]);

        // 登录后需要返回的数据
        $data = [
            'userInfo' => $user,
            'menus' => $tree
        ];

        return Result::success($data);
    }

}