<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\User;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\WechatService;
use App\Result;

class InviteChannelController extends Controller
{

    public function __construct()
    {
        throw new ParamInvalidException('邀请用户功能已关闭');
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getInviteQrcode()
    {
        $operId = request()->get('current_oper')->id;
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteService::getInviteChannel($userId, InviteChannel::ORIGIN_TYPE_USER, $operId);
        $inviteChannel->origin_name = InviteService::getInviteChannelOriginName($inviteChannel);
        $scene = MiniprogramScene::findOrFail($inviteChannel->scene_id);
        $url = WechatService::getMiniprogramAppCodeUrl($scene);

        return Result::success([
            'qrcode_url' => $url,
            'inviteChannel' => $inviteChannel
        ]);
    }

    /**
     * 根据场景ID获取邀请人信息
     */
    public function getInviterBySceneId()
    {
        $sceneId = request('sceneId');
        if(empty($sceneId)){
            throw new ParamInvalidException('场景ID不能为空');
        }
        // 判断场景类型必须是 推广注册小程序码 才可以
        $scene = MiniprogramScene::findOrFail($sceneId);
        if($scene->type != MiniprogramScene::TYPE_INVITE_CHANNEL){
            throw new ParamInvalidException('该场景不是邀请渠道场景');
        }
        $inviteChannel = InviteChannel::where('scene_id', $sceneId)->first();
        if(empty($inviteChannel)){
            throw new ParamInvalidException('场景不存在');
        }

        $inviteChannel->origin_name = InviteService::getInviteChannelOriginName($inviteChannel);
        return Result::success($inviteChannel);
    }

    /**
     * 绑定推荐人
     */
    public function bindInviter()
    {
        $inviteChannelId = request('inviteChannelId');
        $inviteChannel = InviteChannel::find($inviteChannelId);
        if(empty($inviteChannel)){
            throw new ParamInvalidException('邀请渠道不存在');
        }
        InviteService::bindInviter(request()->get('current_user')->id, $inviteChannel);
        return Result::success();
    }

}