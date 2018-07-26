<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/26
 * Time: 14:52
 */

namespace App\Modules\Wechat;


use App\BaseService;
use App\Exceptions\DataNotFoundException;
use App\Modules\Invite\InviteChannel;
use App\Modules\Order\Order;

class MiniprogramSceneService extends BaseService
{

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
        $miniprogramScene = MiniprogramScene::where('invite_channel_id', $inviteChannelId)
            ->where('oper_id', $operId)
            ->orderBy('id', 'desc')
            ->first();
        if (empty($miniprogramScene)) {
            throw new DataNotFoundException('该邀请渠道的小程序场景不存在');
        }
        return $miniprogramScene;
    }

    /**
     * 添加场景
     * @param $operId
     * @param $merchantId
     * @param $inviteChannelId
     * @param $page
     * @param $payload
     * @param int $type
     * @return MiniprogramScene
     */
    protected static function add($operId, $merchantId, $inviteChannelId, $page, $payload, $type = 1): MiniprogramScene
    {
        $miniprogramScene = new MiniprogramScene();
        $miniprogramScene->oper_id = $operId;
        $miniprogramScene->merchant_id = $merchantId;
        $miniprogramScene->invite_channel_id = $inviteChannelId;
        $miniprogramScene->page = $page;
        $miniprogramScene->type = $type;
        $miniprogramScene->payload = $payload;
        $miniprogramScene->save();

        return $miniprogramScene;
    }

    public static function createPayBridgeScene(Order  $order)
    {
        // todo
    }

    public static function createInviteScene(InviteChannel $inviteChannel)
    {
        $miniprogramScene = new MiniprogramScene();
        $miniprogramScene->oper_id = $inviteChannel->oper_id;
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

    public static function createScanPayScene(int $merchantId)
    {
        // todo
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
    public static function edit($sceneId, $operId, $merchantId, $inviteChannelId, $page, $type = 1, $payload, $qrcodeUrl = ''): MiniprogramScene
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
    }
}