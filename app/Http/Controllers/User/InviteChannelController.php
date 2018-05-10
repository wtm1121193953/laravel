<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
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

}