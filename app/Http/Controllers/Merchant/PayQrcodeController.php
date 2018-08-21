<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 11:20
 */

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\PayQrcodeService;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Modules\Wechat\WechatService;
use App\Result;

class PayQrcodeController extends Controller
{

    /**
     * 获取支付小程序码
     */
    public function getMiniprogramAppCode()
    {
        $merchantId = request()->get('current_user')->merchant_id;

        $scene = MiniprogramSceneService::getPayAppCodeByMerchantId($merchantId);
        $qrcode_url = WechatService::getMiniprogramAppCodeUrl($scene);

        return Result::success([
            'qrcode_url' => $qrcode_url,
        ]);
    }

    /**
     * 下载扫码支付的小程序码
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @deprecated todo 去掉下载二维码操作, 由统一下载控制器下载
     */
    public function downloadMiniprogramAppCode()
    {
        $type = request('type', 1);

        $merchantId = request()->get('current_user')->merchant_id;

        $scene = MiniprogramSceneService::getPayAppCodeByMerchantId($merchantId);

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);

        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getSignboardNameById($merchantId);

        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '支付小程序码_' . ['', '小', '中', '大'][$type] . '.jpg');
    }
}