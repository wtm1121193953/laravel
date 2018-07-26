<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/12
 * Time: 23:04
 */

namespace App\Modules\Invite;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Jobs\MerchantLevelCalculationJob;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\ResultCode;

/**
 * 用户邀请相关服务
 * Class InviteService
 * @package App\Modules\Invite
 */
class InviteService
{

    /**
     * 获取用户上级, 可能是用户/商户或运营中心
     * @param $userId
     * @return Merchant|Oper|User|null
     */
    public static function getParent($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if(empty($inviteRecord)){
            // 如果没有用户没有上级, 不做任何处理
            return null;
        }
        if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            $object = Merchant::where('id', $inviteRecord->origin_id)->first();
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER){
            $object = Oper::where('id', $inviteRecord->origin_id)->first();
        }else {
            $object = User::find($inviteRecord->origin_id);
        }
        return $object;
    }

    /**
     * 获取上级用户
     * @param $userId
     * @return User
     */
    public static function getParentUser($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if(empty($inviteRecord)){
            // 如果没有用户没有上级, 不做任何处理
            return null;
        }
        if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_MERCHANT)
                ->first();
            if(empty($userMapping)){
                return null;
            }
            $user = User::find($userMapping->user_id);
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER){
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_OPER)
                ->first();
            if(empty($userMapping)){
                return null;
            }
            $user = User::find($userMapping->user_id);
        }else {
            $user = User::find($inviteRecord->origin_id);
        }
        return $user;
    }

    /**
     * 绑定邀请人信息到用户
     * @param $userId
     * @param InviteChannel $inviteChannel
     */
    public static function bindInviter($userId, InviteChannel $inviteChannel)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if($inviteRecord){
            // 如果当前用户已被邀请过, 不能重复邀请
            throw new BaseResponseException('您已经被邀请过了, 不能重复接收邀请', ResultCode::USER_ALREADY_BEEN_INVITE);
        }
        if($inviteChannel->origin_type == InviteChannel::ORIGIN_TYPE_USER && $inviteChannel->origin_id == $userId){
            throw new ParamInvalidException('不能扫描自己的邀请码');
        }
        $inviteRecord = new InviteUserRecord();
        $inviteRecord->user_id = $userId;
        $inviteRecord->invite_channel_id = $inviteChannel->id;
        $inviteRecord->origin_id = $inviteChannel->origin_id;
        $inviteRecord->origin_type = $inviteChannel->origin_type;
        $inviteRecord->save();

        if ($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            MerchantLevelCalculationJob::dispatch($inviteRecord->origin_id);
        }
    }

}