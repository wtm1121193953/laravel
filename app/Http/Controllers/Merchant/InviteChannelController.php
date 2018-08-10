<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
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

        $scene = MiniprogramSceneService::getMerchantInviteChannelScene($currentUser->merchant_id, $currentUser->oper_id);

        $url = MiniprogramSceneService::getMiniprogramAppCode($scene);

        $signboardName = MerchantService::getMerchantValueByIdAndKey($currentUser->merchant_id, 'signboard_name');
        WechatService::addNameToAppCode($url, $signboardName);

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

        $scene = MiniprogramSceneService::getMerchantInviteChannelScene($currentUser->merchant_id, $currentUser->oper_id);

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getMerchantValueByIdAndKey($currentUser->merchant_id, 'signboard_name');
        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '分享会员二维码_' . ['', '小', '中', '大'][$type] . '.jpg');

    }
}