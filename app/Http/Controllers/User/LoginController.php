<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:15
 */

namespace App\Http\Controllers\User;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\User\User;
use App\Modules\User\UserOpenIdMapping;
use App\Result;
use Carbon\Carbon;

class LoginController extends Controller
{

    public function login()
    {
        $this->validate(request(), [
            'mobile' => 'required|size:11',
            'verify_code' => 'required|size:4',
        ]);
        $mobile = request('mobile');
        if(!preg_match('/^1[3,4,5,6,7,8]\d{9}/', $mobile)){
            throw new ParamInvalidException('手机号码不合法');
        }
        $verifyCode = request('verify_code');
        $verifyCodeRecord = SmsVerifyCode::where('mobile', $mobile)
            ->where('verify_code', $verifyCode)
            ->where('status', 1)
            ->where('expire_time', '<', Carbon::now())
            ->first();
        if(empty($verifyCodeRecord)){
            throw new ParamInvalidException('验证码错误');
        }
        $verifyCodeRecord->status = 2;
        $verifyCodeRecord->save();

        // 验证通过, 绑定openId到当前用户
        if(! $user = request()->get('current_user')){
            $user = new User();
            $user->mobile = $mobile;
            $user->save();
        }
        // 保存用户与openId的映射关系, 并覆盖旧的关联关系
        $openId = request()->get('current_open_id');
        $userOpenIdMapping = UserOpenIdMapping::where('open_id', $openId)->first();
        if($userOpenIdMapping){
            $userOpenIdMapping->user_id = $user->id;
            $userOpenIdMapping->oper_id = request()->get('current_oper')->id;
            $userOpenIdMapping->save();
        }else {
            $userOpenIdMapping = new UserOpenIdMapping();
            $userOpenIdMapping->oper_id = request()->get('current_oper')->id;
            $userOpenIdMapping->open_id = $openId;
            $userOpenIdMapping->user_id = $user->id;
            $userOpenIdMapping->save();
        }
        return Result::success([
            'userInfo' => $user
        ]);
    }
}