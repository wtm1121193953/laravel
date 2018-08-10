<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 15:29
 */

namespace App\Modules\Email;

use App\BaseService;
use Illuminate\Support\Carbon;

use App\Support\TpsApi;

class EmailService extends BaseService
{
    /**
     * 发送邮件
     * @param $email
     * @param $verifyCode
     * @return mixed|string
     */
    public static function sendVerifyCode($email)
    {
    	$reMsg = '发送成功！';
    	$verifyCode = rand(1000, 9999);
    	$title = $content = '您正在生成TPS账号，验证码是：'.$verifyCode.', 3分钟内有效';
    	
    	$result = TpsApi::sendEmail($email, $title, $content);
    	$result = json_decode($result, true);

    	//返回发送失败信息到页面
    	if($result['code'] != '000'){
    		
    		$reMsg = $result['msg'];
    		
    	}
    	else{
    		
    		$record = new EmailVerifyCode();
    		$record->email = $email;
    		$record->verify_code = $verifyCode;
    		$record->type = 1;
    		$record->status = 1;
    		$record->expire_time = Carbon::now()->addMinutes(3);
    		$record->save();
    	}

    	return $reMsg;
    }
    
    /**
     * 判断验证码是否有效
     * @param $email
     * @param $verify_code
     * @param $type
     * @return object
     */
    public static function judgeVcode($email, $verify_code, $type=1)
    {
    	// todo 根据tps账号获取该tps账号绑定的信息
    	return EmailVerifyCode::where('email', $email)
    	->where('verify_code', $verify_code)
    	->where('type', $type)
    	->where('expire_time', '>', date('Y-m-d H:i:s'))
    	->orderBy('expire_time', 'desc')
    	->first();
    	
    }

}