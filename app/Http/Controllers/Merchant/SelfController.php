<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 14:58
 */

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantAccountService;
use App\Modules\Merchant\MerchantService;
use App\Result;

class SelfController extends Controller
{

    /**
     * 登录
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
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

        $user = MerchantAccountService::login($username,$password);
        $menus = MerchantAccountService::getMenus($user->oper_id);

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    /**
     * 登出
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        MerchantAccountService::logout();
        return Result::success();
    }

    /**
     * 修改密码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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

        $user = MerchantAccountService::modifyPassword($user,$password,$newPassword);

        return Result::success($user);
    }


    /**
     * 获取商户信息
     */
    public function getMerchantInfo(){
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = MerchantService::detail($merchantId);

        return Result::success($merchant);
    }
}