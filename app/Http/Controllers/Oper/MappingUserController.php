<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/28
 * Time: 21:46
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Result;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MappingUserController extends Controller
{
    public function getMappingUser()
    {
        $origin_id = request()->get('current_user')->oper_id;
        $origin_type = 2;

        $mapping_user = UserMapping::where('origin_id', $origin_id)
            ->where('origin_type', $origin_type)
            ->first();

        return Result::success($mapping_user);
    }

    public function getUser()
    {
        $this->validate(request(),  [
            'id' => 'required'
        ]);

        $id = request('id');
        $user = User::findOrFail($id);

        return Result::success($user);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function operBindUser()
    {
        $this->validate(request(), [
            'mobile' => 'required|size:11',
            'verify_code' => 'required|size:4',
        ]);
        $mobile = request('mobile');
        if(!preg_match('/^1[3,4,5,6,7,8,9]\d{9}/', $mobile)){
            throw new ParamInvalidException('手机号码不合法');
        }

        if($user = User::where('mobile', $mobile)->first()){
            $mappingUser = UserMapping::where('user_id', $user->id)->first();
            if (!empty($mappingUser)){
                throw new BaseResponseException('该手机号码已被绑定，请更换手机号码重试');
            }

            $inviteUserRecord = InviteUserRecord::where('user_id', $user->id)
                ->where('origin_id', request()->get('current_user')->oper_id)
                ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_OPER)
                ->first();
            if (!empty($inviteUserRecord)){
                throw new BaseResponseException('不能绑定该运营中心所邀请的用户');
            }
        }

        $verifyCode = request('verify_code');
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
        $merchant = Oper::findOrFail(request()->get('current_user')->oper_id);
        $merchant->mapping_user_id = $user->id;
        $merchant->save();

        //用户创建后，添加 用户与商户及运营中心映射
        $userMapping = new UserMapping();
        $userMapping->origin_id = request()->get('current_user')->oper_id;
        $userMapping->origin_type = 2;
        $userMapping->user_id = $user->id;
        $userMapping->save();

        DB::commit();
        return Result::success([
                'userInfo' => $user
        ]);
    }
}