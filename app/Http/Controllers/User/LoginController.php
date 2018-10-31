<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:15
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Modules\User\UserOpenIdMapping;
use App\Modules\Wechat\MiniprogramScene;
use App\Result;
use App\Support\RedisLock;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // 获取锁
        $lock_key = 'user_register:' . $mobile;
        $is_lock = RedisLock::lock($lock_key, 5);

        if($is_lock){
            // 开始事务, 如果登陆失败, 验证码回滚为为验证状态
            DB::beginTransaction();
            try {

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

                $wxUserInfo = json_decode(request('userInfo'));
                // 验证通过, 查询当前用户是否存在, 不存在则创建用户
                $user = User::where('mobile', $mobile)->first();
                $isFirstSign = null;
                if(!$user){
                    $user = new User();
                    $user->mobile = $mobile;
                    //判断是否是新用户注册，1：是，null：不是
                    $isFirstSign = 1;
                }
                if ($wxUserInfo) {
                    $user->wx_nick_name = $wxUserInfo->nickName;
                    $user->wx_avatar_url = $wxUserInfo->avatarUrl;
                }
                $user->save();
                // 重新查一次用户信息, 补充用户信息中的全部字段
                $user = User::find($user->id);

                // 如果存在邀请渠道ID, 查询用户是否已被邀请过
                $inviteChannelId = request('inviteChannelId');
                if($inviteChannelId){
                    $inviteChannel = InviteChannelService::getById($inviteChannelId);
                    if(empty($inviteChannel)){
                        throw new ParamInvalidException('邀请渠道不存在');
                    }
                    InviteUserService::bindInviter($user->id, $inviteChannel);
                }

                // 保存用户与openId的映射关系, 并覆盖旧的关联关系
                $openId = request()->get('current_open_id');
                $userOpenIdMapping = UserOpenIdMapping::where('open_id', $openId)->first();
                if($userOpenIdMapping){
                    $userOpenIdMapping->user_id = $user->id;
                    $userOpenIdMapping->oper_id = request()->get('current_oper_id');
                    $userOpenIdMapping->save();
                }else {
                    $userOpenIdMapping = new UserOpenIdMapping();
                    $userOpenIdMapping->oper_id = request()->get('current_oper_id');
                    $userOpenIdMapping->open_id = $openId;
                    $userOpenIdMapping->user_id = $user->id;
                    $userOpenIdMapping->save();
                }
                DB::commit();
                RedisLock::unlock($lock_key);
            }catch (\Exception $e){
                DB::rollBack();
                RedisLock::unlock($lock_key);
                throw $e;
            }
        }else{
           throw new BaseResponseException('重复请求');
        }


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
        $user->sign_status = $isFirstSign;
        // 添加传送消息到出纸机
        if($isFirstSign==1 && $inviteChannelId && $inviteChannel){
            $paperMachine = new \App\Support\PaperMachine();
            $paperMachine->createUserId($wxUserInfo);
            $paperMachine->send('http://pay.gxzhijinji.com/api/merb/theGreatLifePlaceOrder');
        }
        return Result::success([
            'userInfo' => $user
        ]);
    }

    public function loginWithSceneId()
    {
        $this->validate(request(), [
            'sceneId' => 'required'
        ]);
        $sceneId = request('sceneId');
        $scene = MiniprogramScene::findOrFail($sceneId);
        if($scene->type != 1){
            throw new BaseResponseException('场景类型不匹配');
        }
        $payload = json_decode($scene->payload, 1);
        if(!$payload || !$payload['user_id'] || !$payload['order_no']){
            throw new BaseResponseException('payload数据错误');
        }
        $user = User::findOrFail($payload['user_id']);
        // 保存用户与openId的映射关系, 并覆盖旧的关联关系
        $openId = request()->get('current_open_id');
        $userOpenIdMapping = UserOpenIdMapping::where('open_id', $openId)->first();
        if($userOpenIdMapping){
            $userOpenIdMapping->user_id = $payload['user_id'];
            $userOpenIdMapping->oper_id = request()->get('current_oper_id');
            $userOpenIdMapping->save();
        }else {
            $userOpenIdMapping = new UserOpenIdMapping();
            $userOpenIdMapping->oper_id = request()->get('current_oper_id');
            $userOpenIdMapping->open_id = $openId;
            $userOpenIdMapping->user_id = $payload['user_id'];
            $userOpenIdMapping->save();
        }

        $user->level_text = User::getLevelText($user->level);
        return Result::success([
            'userInfo' => $user,
            'payload' => $payload,
        ]);
    }

    /**
     * 退出登录
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function logout()
    {
        // 解除openId关联
        $openId = request()->get('current_open_id');
        $userOpenIdMapping = UserOpenIdMapping::where('open_id', $openId)->first();
        if($userOpenIdMapping){
            $userOpenIdMapping->delete();
        }
        return Result::success();
    }
}