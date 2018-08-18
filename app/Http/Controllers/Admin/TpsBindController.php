<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Tps\TpsBind;
use App\Result;
use App\Modules\Tps\TpsBindService;
use App\Modules\Email\EmailService;

class TpsBindController extends Controller
{

    public function getBindInfo()
    {

        //获取当前登录的帐号信息
        $originId = request()->get('current_user')->oper_id;
        //运营中心类型
        $bindInfo = TpsBindService::getTpsBindInfoByOriginInfo($originId, TpsBind::ORIGIN_TYPE_OPER);

        return Result::success($bindInfo);

    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function bindAccount()
    {

        $this->validate(request(), [
            'email' => 'required',
            'verifyCode' => 'required',
        ]);
        //获取传参
        $email = request('email');
        $verifyCode = request('verifyCode');

        //先判断验证码是否有效，仅限后缀@yunke138.com官方邮箱注册
        if (substr($email, -13) != '@yunke138.com') {
            throw new ParamInvalidException('仅限后缀@yunke138.com官方邮箱注册');
        }

        $emailVerifyCode = EmailService::checkVerifyCode($email, $verifyCode);
        if (!$emailVerifyCode) {
            //y验证码无效
            throw new ParamInvalidException('未检测到有效验证码！');
        }

        //然后判断是否绑定过
        $operId = request('operId');

        $bindInfo = TpsBindService::bindTpsAccountForOper($operId, $email);
        return Result::success($bindInfo);
    }

    public function sendVerifyCode()
    {

        $this->validate(request(), [
            'email' => 'required',
        ]);

        $email = request('email');
        EmailService::sendVerifyCode($email);

        return Result::success();
    }
}