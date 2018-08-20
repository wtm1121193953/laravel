<?php

namespace App\Modules\User;

use App\BaseModel;
use App\Exceptions\ParamInvalidException;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\MerchantService;
use App\Modules\Sms\SmsVerifyCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * Class UserMapping
 * @package App\Modules\User
 *
 * @property int    origin_id
 * @property int    origin_type
 * @property int    user_id
 *
 */
class UserMappingService extends BaseModel
{
    public static function getMappingUser($origin_id){

        $userMapping = UserMapping::where('origin_id', $origin_id)
            ->where('origin_type', UserMapping::ORIGIN_TYPE_MERCHANT)
            ->first();

        $user = User::findOrFail($userMapping->user_id);

        return $user;
    }

    public static function bindUser($merchantId,$mobile,$verifyCode){

        if($user = User::where('mobile', $mobile)->first()){
            $mappingUser = UserMapping::where('user_id', $user->id)->first();
            if (!empty($mappingUser)){
                throw new BaseResponseException('该手机号码已被绑定，请更换手机号码重试');
            }

            $inviteUserRecord = InviteUserRecord::where('user_id', $user->id)
                ->where('origin_id', $merchantId)
                ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
                ->first();
            if (!empty($inviteUserRecord)){
                throw new BaseResponseException('不能绑定该商户所邀请的用户');
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

        $merchant = MerchantService::getById($merchantId);
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchant->mapping_user_id = $user->id;
        $merchant->save();

        //用户创建后，添加 用户与商户及运营中心映射
        $userMapping = new UserMapping();
        $userMapping->origin_id = $merchantId;
        $userMapping->origin_type = 1;
        $userMapping->user_id = $user->id;
        $userMapping->save();

        DB::commit();

        return $user;
    }
}
