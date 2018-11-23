<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/9
 * Time: 23:36
 */

namespace App\Http\Controllers\Cs;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use App\Result;
use App\Support\Utils;

class InviteChannelController extends Controller
{

    /**
     * 获取邀请二维码的链接
     */
    public function getInviteQrcode()
    {
        $currentUser = request()->get('current_user');

        $scene = MiniprogramSceneService::getCsMerchantInviteChannelScene($currentUser->merchant_id, $currentUser->oper_id);

        $signboardName = CsMerchantService::getSignboardNameById($currentUser->merchant_id);
        $url = MiniprogramSceneService::genSceneQrCode($scene, 375,false,$signboardName);

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

        $scene = MiniprogramSceneService::getCsMerchantInviteChannelScene($currentUser->merchant_id, $currentUser->oper_id);

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $signboardName = CsMerchantService::getSignboardNameById($currentUser->merchant_id);
        $filePath = MiniprogramSceneService::genSceneQrCode($scene, $width,true,$signboardName);

        return response()->download($filePath, '分享用户二维码_' . ['', '小', '中', '大'][$type] . '.jpg');


    }
}