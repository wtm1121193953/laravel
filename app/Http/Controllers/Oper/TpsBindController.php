<?php

namespace App\Http\Controllers\Oper;

//use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Controller;
use App\Result;
use App\Modules\Tps\TpsBindService;
use App\Modules\Email\EmailService;

class TpsBindController extends Controller{
	
	public function getBindInfo(){
		
		$tpsBindService = new TpsBindService();
		
		//获取当前登录的账号信息
		$originId = request()->get('current_user')->oper_id;
		//运营中心类型=3
		$originType = 3;
		$tpsData = $tpsBindService::getTpsBindInfoByOriginInfo($originId, $originType);
		
		$bindAccount = '';
		if(!empty($tpsData)){
			$bindAccount = $tpsData;
		}

		return Result::success([
				'bindAccount' => $bindAccount,
		]);
		
	}
	
	public function bindAccount(){
		
		//获取传参
		$email = request('email');
		$code = request('code');
		
		//先判断验证码是否有效，仅限后缀@shoptps.com官方邮箱注册
		if(substr($email,-12) != '@shoptps.com'){
			return Result::success([
					'msg' => '仅限后缀@shoptps.com官方邮箱注册',
			]);
		}

		$emailService = new EmailService();
		$emailData = $emailService::judgeVcode($email, $code);
		if(empty($emailData)){
			//y验证码无效
			return Result::success([
					'msg' => '未检测到有效验证码！',
			]);
		}
		
		//然后判断是否绑定过
		$tpsBindService = new TpsBindService();
		$tpsbindsData = $tpsBindService::getTpsBindInfoByTpsAccount($email);
		if(empty($tpsbindsData)){
			//未绑定过
			$originId = request()->get('current_user')->oper_id;
			$tpsBindService::bindTpsAccountForOper($originId, $email);
			return Result::success([
					'msg' => '生成成功',
			]);
			
		}else{
			//已绑定过
			return Result::success([
					'msg' => '生成失败，邮箱已存在TPS',
			]);
		}

	}
	
	public function getVcode(){
		
		$email = request('email');
		$emailService = new EmailService();
		$sendResult = $emailService::sendVerifyCode($email);
		
		return Result::success([
				'msg'  => $sendResult,
		]);
	}
}