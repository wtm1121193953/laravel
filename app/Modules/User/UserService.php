<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:20
 */

namespace App\Modules\User;

use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Invite\InviteUserService;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Sms\SmsVerifyCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService
{


    /**
     * @param $mobile
     * @param $verifyCode
     * @return array
     * @throws \Exception
     */
    public static function userAppLogin($mobile, $verifyCode)
    {
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
            $inviteChannel = InviteChannelService::getById($inviteChannelId);
            if(empty($inviteChannel)){
                throw new ParamInvalidException('邀请渠道不存在');
            }
            InviteUserService::bindInviter($user->id, $inviteChannel);
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

        return [
            'user' => $user,
            'token' => $token
        ];
    }
    /**
     * 获取会员列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params){

        $mobile = array_get($params, 'mobile');

        $users  = User::select('id','name','mobile','email','created_at')
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('mobile','like','%'.$mobile.'%');
            })
            ->orderByDesc('created_at')
            ->paginate();

        $users->each(function ($item){

            $parentName = InviteUserService::getParentName($item->id);
            if($parentName){
                $item->isBind = 1;
                $item->parent = $parentName;
            }else {
                $item->parent = '未绑定';
                $item->isBind = 0;
            }
        });

        return $users;
    }
    /**
     * 通过电话号码查询用户详情
     * @param $mobile
     * @return User
     */
    public static function getUserByMobile($mobile)
    {
        $user = User::where('mobile', $mobile)->first();
        return $user;
    }

    /**
     * 通过id获取用户信息
     * @param $userId
     * @return User
     */
    public static function getUserById($userId)
    {
        $user = User::find($userId);

        return $user;
    }

    /**
     * 根据openId获取用户信息
     * @param $openId
     * @return User|null
     */
    public static function getUserByOpenId($openId)
    {
        $userId = UserOpenIdMapping::where('open_id', $openId)->value('user_id');
        if($userId){
            $user = UserService::getUserById($userId);
            return $user;
        }
        return null;
    }
}