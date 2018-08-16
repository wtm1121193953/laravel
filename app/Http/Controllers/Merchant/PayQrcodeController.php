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
        $operId = request()->get('current_user')->oper_id;
        $qrcode_url = PayQrcodeService::getMiniprogramAppCode($merchantId,$operId);
        return Result::success([
            'qrcode_url' => $qrcode_url,
        ]);
    }

    /**
     * 下载扫码支付的小程序码
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadMiniprogramAppCode()
    {
        $type = request('type', 1);

        $merchantId = request()->get('current_user')->merchant_id;

        $scene = PayQrcodeService::downloadMiniprogramAppCode($merchantId);

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);

        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getMerchantValueByIdAndKey($merchantId, 'signboard_name');

        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '支付小程序码_' . ['', '小', '中', '大'][$type] . '.jpg');
    }
}