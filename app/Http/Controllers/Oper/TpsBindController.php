<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Tps\TpsBind;
use App\Result;
use App\Modules\Tps\TpsBindService;
use App\Modules\Email\EmailService;

class TpsBindController extends Controller{
	
	public function getBindInfo(){
		
		$tpsBindService = new TpsBindService();
		
		//获取当前登录的账号信息
		$originId = request()->get('current_user')->oper_id;
		//运营中心类型
		$tpsData = $tpsBindService::getTpsBindInfoByOriginInfo($originId, TpsBind::ORIGIN_TYPE_OPER);
		
		$bindAccount = '';
		if(!empty($tpsData)){
			$bindAccount = $tpsData;
		}

		return Result::success([
            'bindAccount' => $bindAccount,
		]);
		
	}

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function bindAccount(){
		
		//获取传参
		$email = request('email');
		$vcode = request('vcode');
		
		//先判断验证码是否有效，仅限后缀@shoptps.com官方邮箱注册
		if(substr($email,-12) != '@shoptps.com'){
            throw new ParamInvalidException('仅限后缀@shoptps.com官方邮箱注册');
		}

		$emailVerifyCode = EmailService::checkVerifyCode($email, $vcode);
		if(!$emailVerifyCode){
			//y验证码无效
            throw new ParamInvalidException('未检测到有效验证码！');
		}
		
		//然后判断是否绑定过
        $operId = request()->get('current_user')->oper_id;
        $tpsBind = TpsBindService::bindTpsAccountForOper($operId, $email);
        return Result::success($tpsBind);
	}
	
	public function getVcode(){
		
		$email = request('email');
		EmailService::sendVerifyCode($email);
		
		return Result::success();
	}
}