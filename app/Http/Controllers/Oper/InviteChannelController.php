<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteService;
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
        $operId = request()->get('current_user')->oper_id;
        $inviteChannel = InviteService::getInviteChannel($operId, InviteChannel::ORIGIN_TYPE_OPER, $operId);
        $scene = MiniprogramScene::findOrFail($inviteChannel->scene_id);
        $url = WechatService::getMiniprogramAppCodeUrl($scene);
        return Result::success([
            'qrcode_url' => $url,
        ]);
    }

    /**
     * 下载邀请注册的小程序码
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function downloadInviteQrcode()
    {
        // type 小程序码类型, 1-小(8cm, 对应258px) 2-中(15cm, 对应430px)  3-大(50cm, 对应1280px)
        $type = request('type', 1);
        $operId = request()->get('current_user')->oper_id;
        $inviteChannel = InviteChannel::where('oper_id', $operId)
            ->where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->firstOrFail();
        $scene = MiniprogramScene::findOrFail($inviteChannel->scene_id);
        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $inviteQrcodeFilename = WechatService::genMiniprogramAppCode($operId, $scene->id, $scene->page, $width, true);
        $filename = storage_path('app/public/miniprogram/app_code') . '/' . $inviteQrcodeFilename;
        return response()->download($filename, '分享会员二维码_' . ['', '小', '中', '大'][$type] . '.jpg');

    }
}