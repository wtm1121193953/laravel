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

        if (empty($scene->qrcode_url)) {
            $url = MiniprogramSceneService::getMiniprogramAppCode($scene);

            $signboardName = MerchantService::getSignboardNameById($currentUser->merchant_id);

            $fileName = pathinfo($url, PATHINFO_BASENAME);
            $path = storage_path('app/public/miniprogram/app_code/') . $fileName;
            WechatService::addNameToAppCode($path, $signboardName);
        } else {
            $url = $scene->qrcode_url;
        }


        return Result::success([
            'qrcode_url' => $url,
        ]);
    }

    /**
     * 下载邀请注册的小程序码
     * @deprecated
     * todo 去掉下载二维码操作, 使用统一的下载控制器下载图片
     */
    public function downloadInviteQrcode()
    {
        // type 小程序码类型, 1-小(8cm, 对应258px) 2-中(15cm, 对应430px)  3-大(50cm, 对应1280px)
        $type = request('type', 1);
        $currentUser = request()->get('current_user');

        $scene = MiniprogramSceneService::getMerchantInviteChannelScene($currentUser->merchant_id, $currentUser->oper_id);

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getSignboardNameById($currentUser->merchant_id);
        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '分享会员二维码_' . ['', '小', '中', '大'][$type] . '.jpg');

    }
}