<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 17:38
 */

namespace App\Modules\Oper;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class OperMappingUserService extends BaseService
{

    public static function getMappingUser($origin_id,$origin_type)
    {
        $userMapping = UserMapping::where('origin_id', $origin_id)
            ->where('origin_type', $origin_type)
            ->first();
        if(empty($userMapping)){
            return Result::success();
        }
        $user = User::findOrFail($userMapping->user_id);

        return $user;
    }

    public static function getUser($id){
        $user = User::findOrFail($id);
        return $user;
    }

    public static function getOperBindUser($mobile,$operId,$verifyCode){

        if($user = User::where('mobile', $mobile)->first()){
            $mappingUser = UserMapping::where('user_id', $user->id)->first();
            if (!empty($mappingUser)){
                throw new BaseResponseException('该手机号码已被绑定，请更换手机号码重试');
            }

            $inviteUserRecord = InviteUserRecord::where('user_id', $user->id)
                ->where('origin_id', $operId)
                ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_OPER)
                ->first();
            if (!empty($inviteUserRecord)){
                throw new BaseResponseException('不能绑定该运营中心所邀请的用户');
            }
        }

        // 开始事务, 如果登陆失败, 验证码回滚为为验证状态
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
        }

        //商户merchants表 关联user_id
        $merchant = Oper::findOrFail($operId);
        $merchant->mapping_user_id = $user->id;
        $merchant->save();

        //用户创建后，添加 用户与商户及运营中心映射
        $userMapping = new UserMapping();
        $userMapping->origin_id = $operId;
        $userMapping->origin_type = 2;
        $userMapping->user_id = $user->id;
        $userMapping->save();

        DB::commit();

        return $user;
    }

}