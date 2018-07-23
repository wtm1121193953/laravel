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
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperAccount;
use App\Result;
use Illuminate\Support\Facades\Session;

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
        $user = OperAccount::where('account', request('username'))->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(OperAccount::genPassword(request('password'), $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }
        if($user->status != 1){
            throw new NoPermissionException('帐号已被禁用');
        }
        // 检查运营中心是否被冻结
        $oper = Oper::findOrFail($user->oper_id);
        if($oper->status != 1){
            throw new NoPermissionException('运营中心已被冻结');
        }

        session([
            config('oper.user_session') => $user,
        ]);

        $user->operName = $oper->name;

        $merchantDraftCount = MerchantDraft::where('creator_oper_id', $user->oper_id)->count();
        return Result::success([
            'user' => $user,
            'menus' => $this->getMenus($merchantDraftCount),
        ]);
    }

    public function logout()
    {
        Session::forget(config('oper.user_session'));
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
        // 检查原密码是否正确
        if(OperAccount::genPassword(request('password'), $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $user = OperAccount::findOrFail($user->id);
        $salt = str_random();
        $user->salt = $salt;
        $user->password = OperAccount::genPassword(request('newPassword'), $salt);
        $user->save();

        // 修改密码成功后更新session中的user
        session([
            config('oper.user_session') => $user,
        ]);

        $user->operName = Oper::where('id', $user->oper_id)->value('name');

        return Result::success($user);
    }

    private function getMenus($merchantDraftCount = 0)
    {
        return [
            [ 'id' => 1, 'name' => '商户管理', 'level' => 1, 'url' => 'merchant', 'sub' =>
                [
                    [ 'id' => 2, 'name' => '我的商户', 'level' => 2, 'url' => '/oper/merchants', 'pid' => 1,],
                    [ 'id' => 3, 'name' => '商户池', 'level' => 2, 'url' => '/oper/merchant/pool', 'pid' => 1,],
                    [ 'id' => 13, 'name' => '商户审核记录', 'level' => 2, 'url' => '/oper/merchant/audits', 'pid' => 1],
                    [ 'id' => 14, 'name' => '草稿箱('.$merchantDraftCount.')', 'level' => 2, 'url' => '/oper/merchant/drafts', 'pid' => 1],
                ]

            ],
            [ 'id' => 10, 'name' => '订单管理', 'level' => 1, 'url' => '/oper/orders' ],
            [ 'id' => 4, 'name' => '人员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                [
//                    [ 'id' => 5, 'name' => '我的会员', 'level' => 2, 'url' => '/oper/invite/statistics/daily', 'pid' => 4,],
                    [ 'id' => 6, 'name' => '我的业务员', 'level' => 2, 'url' => '/oper/operBizMembers', 'pid' => 4,],
                ]
            ],

            [ 'id' => 7, 'name' => '财务管理', 'level' => 1, 'url' => '/oper/settlements',],
            [ 'id' => 8, 'name' => '推广渠道', 'level' => 1, 'url' => 'material', 'sub' =>
                [
                    [ 'id' => 9, 'name' => '渠道列表', 'level' => 2, 'url' => '/oper/invite-channel', 'pid' => 8,],
                ]
            ],
            /*[ 'id' => 11, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                [
                    [ 'id' => 12, 'name' => '关联用户', 'level' => 2, 'url' => '/oper/setting/mapping_user', 'pid' => 11],
                ]
            ],*/
        ];
    }

}