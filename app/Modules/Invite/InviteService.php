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
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型 1-用户 2-商户 3-运营中心
     * @param $operId int 运营中心ID, 存在时则生成对应运营中心的小程序码
     * @return InviteChannel
     */
    public static function getInviteChannel($originId, $originType, $operId=0)
    {
        $inviteChannel = InviteChannel::where('origin_id', $originId)
            ->where('oper_id', $operId)
            ->where('origin_type', $originType)
            ->first();
        if(empty($inviteChannel)){
            $inviteChannel = self::createInviteChannel($originId, $originType, $operId);
        }
        return $inviteChannel;
    }

    /**
     * 生成推广渠道
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型 1-用户 2-商户 3-运营中心
     * @param $operId int 运营中心ID, 存在时则生成对应运营中心的小程序码
     * @return InviteChannel
     */
    public static function createInviteChannel($originId, $originType, $operId=0)
    {
        // 不能重复生成
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        if($inviteChannel) {
            return $inviteChannel;
        }
        if($operId > 0){
            // 如果运营中心ID存在, 则生成该运营中心的小程序码场景
            $scene = new MiniprogramScene();
            $scene->oper_id = $operId;
            // 小程序端邀请注册页面地址
            $scene->page = MiniprogramScene::PAGE_INVITE_REGISTER;
            $scene->type = MiniprogramScene::TYPE_INVITE_CHANNEL;
            $scene->payload = json_encode([
                'origin_id' => $originId,
                'origin_type' => $originType,
            ]);
            $scene->save();
            $sceneId = $scene->id;
        }else {
            $sceneId = 0;
        }

        $inviteChannel = new InviteChannel();
        $inviteChannel->oper_id = $operId;
        $inviteChannel->origin_id = $originId;
        $inviteChannel->origin_type = $originType;
        $inviteChannel->scene_id = $sceneId;
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