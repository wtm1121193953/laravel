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
        $operId = request()->get('current_oper')->id;
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->first();
        if(empty($inviteChannel)){
            $inviteChannel = InviteChannel::createInviteChannel($operId, $operId, InviteChannel::ORIGIN_TYPE_USER);
        }
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
        $sceneId = request('scene');
        if(empty($sceneId)){
            throw new ParamInvalidException('场景ID不能为空');
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
            $originName = $user->name ?: $user->mobile;
        }else if($originType == 2){
            $originName = Merchant::where('id', $originId)->value('name');
        }else if($originType == 3){
            $originName = Oper::where('id', $originId)->value('name');
        }
        $inviteChannel->origin_name = $originName;
        return Result::success($inviteChannel);
    }

}