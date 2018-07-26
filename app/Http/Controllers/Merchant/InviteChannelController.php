<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteService;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use App\Result;

class InviteChannelController extends Controller
{

    /**
     * 获取邀请二维码的链接
     */
    public function getInviteQrcode()
    {
        $currentUser = request()->get('current_user');
        $inviteChannel = InviteService::getInviteChannel($currentUser->merchant_id, InviteChannel::ORIGIN_TYPE_MERCHANT, $currentUser->oper_id);
        $scene = MiniprogramSceneService::getByInviteChannel($inviteChannel);
        try{
            $url = WechatService::getMiniprogramAppCodeUrl($scene);
        }catch (\Exception $e){
            throw new BaseResponseException('小程序码生成失败');
        }

        return Result::success([
            'qrcode_url' => $url,
        ]);
    }

    /**
     * 下载邀请注册的小程序码
     */
    public function downloadInviteQrcode()
    {
        // type 小程序码类型, 1-小(8cm, 对应258px) 2-中(15cm, 对应430px)  3-大(50cm, 对应1280px)
        $type = request('type', 1);
        $currentUser = request()->get('current_user');
        $inviteChannel = InviteService::getInviteChannel($currentUser->merchant_id, InviteChannel::ORIGIN_TYPE_MERCHANT, $currentUser->oper_id);
        $scene = MiniprogramSceneService::getByInviteChannel($inviteChannel);
        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $inviteQrcodeFilename = WechatService::genMiniprogramAppCode($currentUser->oper_id, $scene->id, $scene->page, $width, true);
        $filename = storage_path('app/public/miniprogram/app_code') . '/' . $inviteQrcodeFilename;
        return response()->download($filename, '分享会员二维码_' . ['', '小', '中', '大'][$type] . '.jpg');

    }
}