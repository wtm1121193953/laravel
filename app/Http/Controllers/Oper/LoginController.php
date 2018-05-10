<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 0:08
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\PasswordErrorException;
use App\Http\Controllers\Controller;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperAccount;
use App\Result;
use Illuminate\Support\Facades\Session;

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
        if($user->status != 1){
            throw new NoPermissionException('账号已被禁用');
        }
        // 检查运营中心是否被冻结
        $oper = Oper::findOrFail($user->oper_id);
        if($oper->status != 1){
            throw new NoPermissionException('运营中心已被冻结');
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

    public function logout()
    {
        Session::forget(config('oper.user_session'));
        return Result::success();
    }

    private function getMenus()
    {
        return [
            [ 'id' => 1, 'name' => '商户管理', 'level' => 1, 'url' => 'merchant', 'sub' =>
                [
                    [ 'id' => 2, 'name' => '我的商户', 'level' => 2, 'url' => '/oper/merchants', 'pid' => 1,],
                    [ 'id' => 3, 'name' => '商户池', 'level' => 2, 'url' => '/oper/merchant/pool', 'pid' => 1,],
                ]
            ],
            [ 'id' => 4, 'name' => '人员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                [
                    [ 'id' => 5, 'name' => '我的会员', 'level' => 2, 'url' => '/oper/invite/statistics/daily', 'pid' => 4,],
                    [ 'id' => 6, 'name' => '我的业务员', 'level' => 2, 'url' => '/oper/operBizMembers', 'pid' => 4,],
                ]
            ],

            [ 'id' => 7, 'name' => '财务管理', 'level' => 1, 'url' => '/oper/settlements',],
            [ 'id' => 8, 'name' => '素材中心', 'level' => 1, 'url' => 'material', 'sub' =>
                [
                    [ 'id' => 9, 'name' => '分享会员二维码', 'level' => 2, 'url' => '/oper/invite-channel', 'pid' => 8,],
                ]
            ],
        ];
    }

}