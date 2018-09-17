<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/26
 * Time: 14:52
 */

namespace App\Modules\Wechat;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;

class MiniprogramSceneService extends BaseService
{

    /**
     * 根据ID获取小程序场景信息
     * @param int $sceneId
     * @return MiniprogramScene
     */
    public static function getById(int $sceneId) : MiniprogramScene
    {
        return MiniprogramScene::find($sceneId);
    }

    /**
     * 根据邀请渠道获取小程序场景信息
     * @param InviteChannel $inviteChannel
     * @return MiniprogramScene
     */
    public static function getByInviteChannel(InviteChannel $inviteChannel): MiniprogramScene
    {
        return self::getByInviteChannelId($inviteChannel->id, $inviteChannel->oper_id);
    }

    /**
     * 根据渠道ID与运营中心ID获取小程序场景信息
     * @param $inviteChannelId
     * @param $operId
     * @return MiniprogramScene
     */
    public static function getByInviteChannelId($inviteChannelId, $operId): MiniprogramScene
    {
        // 判断是否切换到平台
        // todo
        $oper = OperService::getById($operId);
        if($oper->pay_to_platform!=Oper::PAY_TO_OPER){
            $operId=0;
        }
        $miniprogramScene = MiniprogramScene::where('invite_channel_id', $inviteChannelId)
            ->where('oper_id', $operId)
            ->orderBy('id', 'desc')
            ->first();
        if (empty($miniprogramScene)) {
            $inviteChannel = InviteChannelService::getById($inviteChannelId);
            $miniprogramScene = self::createInviteScene($inviteChannel);
        }
        return $miniprogramScene;
    }

    /**
     * 获取商户邀请渠道的小程序场景
     * @param $merchantId
     * @param $operId
     * @return MiniprogramScene
     */
    public static function getMerchantInviteChannelScene($merchantId, $operId) : MiniprogramScene
    {
        $inviteChannel = InviteChannelService::getByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, $operId);
        $scene = self::getByInviteChannel($inviteChannel);
        return $scene;
    }

    /**
     * 获取小程序码的url 或文件路径
     * @param MiniprogramScene $scene
     * @param int $width
     * @param bool $getAsFilePath
     * @return string
     */
    public static function getMiniprogramAppCode(MiniprogramScene $scene, $width=375, $getAsFilePath=false) : string
    {
        // 判断是否切换到平台
        // todo
        $oper = OperService::getById($scene->oper_id);
        if($oper->pay_to_platform!=Oper::PAY_TO_OPER){
            $scene->oper_id=0;
        }
        if($getAsFilePath){
            $filename = WechatService::genMiniprogramAppCode($scene->oper_id, $scene->id, $scene->page, $width, true);
            $path = storage_path('app/public/miniprogram/app_code') . '/' . $filename;
            return $path;
        }

        if(!empty($scene->qrcode_url)){
            return $scene->qrcode_url;
        }else {
            $url = WechatService::genMiniprogramAppCode($scene->oper_id, $scene->id, $scene->page);
            $scene->qrcode_url = $url;
            $scene->save();
            return $url;
        }
    }

    public static function createPayBridgeScene(Order $order)
    {
        // todo
    }

    /**
     * 从邀请渠道创建小程序场景
     * @param InviteChannel $inviteChannel
     * @return MiniprogramScene
     */
    public static function createInviteScene(InviteChannel $inviteChannel)
    {
        // todo
        $oper = OperService::getById($inviteChannel->id);
        $miniprogramScene = new MiniprogramScene();
        // 判断是否切换到平台
        $miniprogramScene->oper_id = ($oper->pay_to_platform!=Oper::PAY_TO_OPER) ? 0 : $inviteChannel->oper_id;
        $miniprogramScene->invite_channel_id = $inviteChannel->id;
        $miniprogramScene->page = MiniprogramScene::PAGE_INVITE_REGISTER;
        $miniprogramScene->type = MiniprogramScene::TYPE_INVITE_CHANNEL;
        $miniprogramScene->payload = json_encode([
            'origin_id' => $inviteChannel->oper_id,
            'origin_type' => InviteChannel::ORIGIN_TYPE_OPER,
        ]);
        $miniprogramScene->save();

        return $miniprogramScene;
    }

    /**
     * 创建扫码二维码
     * @param int $merchantId
     * @return MiniprogramScene
     */
    public static function createScanPayScene(int $merchantId)
    {
        $merchant = MerchantService::getById($merchantId, 'oper_id');
        if(empty($merchant) || empty($operId = $merchant->oper_id)){
            throw new BaseResponseException('商户信息不存在或商户尚未审核');
        }
        // todo
        $oper = OperService::getById($operId);
        // 判断是否切换到平台
        $operId= ($oper->pay_to_platform!=Oper::PAY_TO_OPER) ? 0 : $operId;
        $scene = new MiniprogramScene();
        $scene->oper_id = $operId;
        $scene->merchant_id = $merchantId;
        $scene->type = MiniprogramScene::TYPE_PAY_SCAN;
        $scene->page = MiniprogramScene::PAGE_PAY_SCAN;
        $scene->payload = json_encode([
            'merchant_id' => $merchantId,
        ]);
        $scene->save();

        return $scene;
    }


    /**
     * 获取商户支付小程序码
     * @param $merchantId
     * @return MiniprogramScene
     */
    public static function getPayAppCodeByMerchantId($merchantId)
    {
        $scene = MiniprogramScene::where('type', MiniprogramScene::TYPE_PAY_SCAN)
            ->where('merchant_id', $merchantId)
            ->first();
        if(empty($scene)){
            $scene = self::createScanPayScene($merchantId);
        }
        return $scene;
    }

    /**
     * 编辑场景
     * @param $sceneId
     * @param $operId
     * @param $merchantId
     * @param $inviteChannelId
     * @param $page
     * @param int $type
     * @param $payload
     * @param string $qrcodeUrl
     * @return MiniprogramScene
     */
    /*public static function edit($sceneId, $operId, $merchantId, $inviteChannelId, $page, $type = 1, $payload, $qrcodeUrl = ''): MiniprogramScene
    {
        $miniprogramScene = MiniprogramScene::find($sceneId);
        if (empty($miniprogramScene)) {
            throw new DataNotFoundException('小程序场景不存在');
        }
        $miniprogramScene->oper_id = $operId;
        $miniprogramScene->merchant_id = $merchantId;
        $miniprogramScene->invite_channel_id = $inviteChannelId;
        $miniprogramScene->page = $page;
        $miniprogramScene->type = $type;
        $miniprogramScene->payload = $payload;
        $miniprogramScene->qrcode_url = $qrcodeUrl;
        $miniprogramScene->save();

        return $miniprogramScene;
    }*/
}