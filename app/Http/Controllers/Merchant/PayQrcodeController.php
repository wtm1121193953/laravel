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
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
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

        $scene = MiniprogramSceneService::getPayAppCodeByMerchantId($merchantId);

        $oper = OperService::getById($operId);

        if (empty($scene->qrcode_url)) {

            //如果是支付到平台生成二维码
            if ($oper->pay_to_platform != Oper::PAY_TO_OPER) {
                $url = MiniprogramSceneService::genSceneQrCode($scene);
            } else {

                $url = WechatService::getMiniprogramAppCodeUrl($scene);

            }

        } else {
            //如果是支付到平台老的分享码换成二维码
            if ( ($oper->pay_to_platform != Oper::PAY_TO_OPER) && strpos($scene->qrcode_url,'scene_qrcode')===false ) {
                $url = MiniprogramSceneService::genSceneQrCode($scene);
            } elseif (($oper->pay_to_platform == Oper::PAY_TO_OPER) && strpos($scene->qrcode_url,'app_code')===false) {
                $url = WechatService::getMiniprogramAppCodeUrl($scene);
            } else {
                $url = $scene->qrcode_url;
            }

        }



        return Result::success([
            'qrcode_url' => $url,
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