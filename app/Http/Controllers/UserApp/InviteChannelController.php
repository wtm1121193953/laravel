<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\UserApp;


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

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getInviteQrcode()
    {
        $userId = request()->get('current_user')->id;
        $inviteChannel = InviteService::getInviteChannel($userId, InviteChannel::ORIGIN_TYPE_USER);
        $scene = MiniprogramScene::findOrFail($inviteChannel->scene_id);
        $url = WechatService::getMiniprogramAppCodeUrl($scene);
        return Result::success([
            'qrcode_url' => $url,
        ]);
    }

    /**
     * 根据场景ID获取邀请人信息
     */
    public function getInviterInfo()
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
        $originType = $inviteChannel->origin_type;
        $originId = $inviteChannel->origin_id;

        $originName = '';
        if($originType == 1){
            $user = User::findOrFail($originId);
            $originName = $user->name ?: $this->_getHalfHideMobile($user->mobile);
        }else if($originType == 2){
            $originName = Merchant::where('id', $originId)->value('name');
        }else if($originType == 3){
            $originName = Oper::where('id', $originId)->value('name');
        }
        $inviteChannel->origin_name = $originName;
        return Result::success($inviteChannel);
    }

    private function _getHalfHideMobile($mobile){
        return substr($mobile, 0, 3) . '****' . substr($mobile, -4);
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