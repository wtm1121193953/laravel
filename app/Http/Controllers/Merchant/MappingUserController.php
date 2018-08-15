<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/28
 * Time: 21:46
 */

namespace App\Http\Controllers\Merchant;

use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\User\UserMappingService;
use App\Result;

class MappingUserController extends Controller
{
    public function getMappingUser()
    {
        $origin_id = request()->get('current_user')->merchant_id;
        $user = UserMappingService::getMappingUser($origin_id);
        return Result::success($user);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function bindUser()
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

        $user = UserMappingService::bindUser($mobile,$verifyCode);

        return Result::success([
                'userInfo' => $user
        ]);
    }
}