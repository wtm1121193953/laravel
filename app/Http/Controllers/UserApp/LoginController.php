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
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        // 非正式环境时, 验证码为6666为通过验证
        if(App::environment('production') || $verifyCode != '6666'){
            $verifyCodeRecord = SmsVerifyCode::where('mobile', $mobile)
                ->where('verify_code', $verifyCode)
                ->where('status', 1)
                ->where('expire_time', '>', Carbon::now())
                ->first();
            if(empty($verifyCodeRecord)){
                throw new ParamInvalidException('验证码错误');
            }
            $verifyCodeRecord->status = 2;
            $verifyCodeRecord->save();
        }

        // 验证通过, 查询当前用户是否存在, 不存在则创建用户
        if(! $user = User::where('mobile', $mobile)->first()){
            $user = new User();
            $user->mobile = $mobile;
            $user->save();
            // 重新查一次用户信息, 补充用户信息中的全部字段
            $user = User::find($user->id);
        }

        // 如果存在邀请渠道ID, 查询用户是否已被邀请过
        $inviteChannelId = request('inviteChannelId');
        if($inviteChannelId){
            $inviteChannel = InviteChannel::find($inviteChannelId);
            if(empty($inviteChannel)){
                throw new ParamInvalidException('邀请渠道不存在');
            }
            InviteService::bindInviter($user->id, $inviteChannel);
        }
        // 生成token并返回
        $token = str_random(64);
        Cache::put('token_to_user_' . $token, $user, 60 * 24 * 30);

        DB::commit();

        $userMapping = UserMapping::where('user_id', $user->id)->first();
        if (!empty($userMapping)){
            if ($userMapping->origin_type == 1){
                $merchant = Merchant::findOrFail($userMapping->origin_id);
                $user->mapping_merchant_name = $merchant->name;
                $user->merchant_level = $merchant->level;
                $user->merchant_level_text = Merchant::getLevelText($merchant->level);
            }else{
                $oper = Oper::findOrFail($userMapping->origin_id);
                $user->mapping_oper_name = $oper->name;
            }
        }
        $user->level_text = User::getLevelText($user->level);
        return Result::success([
            'userInfo' => $user,
            'token' => $token
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