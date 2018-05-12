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
use App\Modules\Wechat\MiniprogramScene;
use App\ResultCode;

/**
 * 用户邀请相关服务
 * Class InviteService
 * @package App\Modules\Invite
 */
class InviteService
{

    /**
     * 根据运营中心ID, originId 以及originType获取邀请渠道 (不存在时创建)
     * @param $operId
     * @param $originId
     * @param $originType
     * @return InviteChannel
     */
    public static function getInviteChannel($operId, $originId, $originType)
    {
        $inviteChannel = InviteChannel::where('origin_id', $originId)
            ->where('oper_id', $operId)
            ->where('origin_type', $originType)
            ->first();
        if(empty($inviteChannel)){
            $inviteChannel = self::createInviteChannel($operId, $originId, $originType);
        }
        return $inviteChannel;
    }

    /**
     * 生成推广渠道
     * @param $operId
     * @param $originId
     * @param $originType
     * @return InviteChannel
     */
    public static function createInviteChannel($operId, $originId, $originType)
    {
        // 不能重复生成
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        if($inviteChannel) {
            return $inviteChannel;
        }
        $scene = new MiniprogramScene();
        $scene->oper_id = $operId;
        // 小程序端邀请注册页面地址
        $scene->page = MiniprogramScene::PAGE_INVITE_REGISTER;
        $scene->type = 2;
        $scene->payload = json_encode([
            'origin_id' => $originId,
            'origin_type' => $originType,
        ]);
        $scene->save();

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $originId;
        $inviteChannel->origin_type = $originType;
        $inviteChannel->scene_id = $scene->id;
        $inviteChannel->save();
        return $inviteChannel;
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
        $inviteRecord->origin_id = $inviteChannel->origin_id;
        $inviteRecord->origin_type = $inviteChannel->origin_type;
        $inviteRecord->save();
    }
}