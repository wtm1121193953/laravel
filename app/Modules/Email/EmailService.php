<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 15:29
 */

namespace App\Modules\Email;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
use Illuminate\Support\Carbon;

use App\Support\TpsApi;
use Illuminate\Support\Facades\Log;

class EmailService extends BaseService
{
    /**
     * 发送邮件
     * @param $email
     * @return bool
     */
    public static function sendVerifyCode($email)
    {
    	$verifyCode = rand(1000, 9999);
    	$title = $content = '您正在生成TPS账号，验证码是：'.$verifyCode.', 10分钟内有效';
    	
    	$result = TpsApi::sendEmail($email, $title, $content);

    	//返回发送失败信息到页面
    	if(isset($result['code']) && $result['code'] == '000'){
            // 将旧的验证码置位无效
            EmailVerifyCode::where('email', $email)
                ->where('type', 1)
                ->where('status', 1)
                ->update([
                    'status' => 2
                ]);
            // 添加新的验证码记录
            $record = new EmailVerifyCode();
            $record->email = $email;
            $record->verify_code = $verifyCode;
            $record->type = 1;
            $record->status = 1;
            $record->expire_time = Carbon::now()->addMinutes(10);
            $record->save();
    	} else{
    	    Log::error('邮件发送失败, result: ', $result);
            throw new BaseResponseException('邮件发送失败', ResultCode::EMAIL_SEND_ERROR);
    	}

    	return true;
    }
    
    /**
     * 判断验证码是否有效
     * @param $email
     * @param $verifyCode
     * @param $type
     * @return EmailVerifyCode|false
     */
    public static function checkVerifyCode($email, $verifyCode, $type = 1)
    {
    	$emailVerifyCode = EmailVerifyCode::where('email', $email)
            ->where('verify_code', $verifyCode)
            ->where('type', $type)
            ->where('status', 1)
            ->where('expire_time', '>', Carbon::now())
            ->first();
    	if($emailVerifyCode){
            $emailVerifyCode->status = 2;
            $emailVerifyCode->save();
            return $emailVerifyCode;
        }else {
    	    return false;
        }
    }

}