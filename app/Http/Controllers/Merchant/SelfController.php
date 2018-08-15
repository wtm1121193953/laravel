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
use App\Result;
use Illuminate\Support\Facades\Session;

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
        Session::forget(config('merchant.user_session'));
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
        $password = request('password');
        $newPassword = request('newPassword');

        $user = MerchantAccountService::modifyPassword($password,$newPassword);

        return Result::success($user);
    }


    /**
     * 获取商户信息
     */
    public function getMerchantInfo(){
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = MerchantAccountService::getMerchantInfo($merchantId);

        return Result::success($merchant);
    }
}