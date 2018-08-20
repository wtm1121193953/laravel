<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:15
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\User\UserService;
use Illuminate\Support\Facades\Cache;
use App\Result;

class LoginController extends Controller
{

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function login()
    {
        $this->validate(request(), [
            'mobile' => 'required|size:11',
            'verify_code' => 'required|size:4',
        ]);
        $mobile = request('mobile');
        if(!preg_match('/^1[3,4,5,6,7,8,9]\d{9}/', $mobile)){
            throw new ParamInvalidException('手机号码不合法');
        }
        $verifyCode = request('verify_code');

        $data = UserService::userAppLogin($mobile,$verifyCode);

        return Result::success([
            'userInfo' => $data['user'],
            'token' => $data['token']
        ]);
    }

    /**
     * 退出登录
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function logout()
    {
        // 清除用户token
        $token = request()->header('token');
        Cache::forget('token_to_user_' . $token);
        return Result::success();
    }
}