<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 11:20
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Wechat\MiniprogramScene;
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
        $scene = MiniprogramScene::where('type', MiniprogramScene::TYPE_PAY_SCAN)
            ->where('merchant_id', $merchantId)
            ->first();
        if(empty($scene)){
            $scene = new MiniprogramScene();
            $scene->oper_id = request()->get('current_user')->oper_id;
            $scene->merchant_id = $merchantId;
            $scene->type = MiniprogramScene::TYPE_PAY_SCAN;
            $scene->page = MiniprogramScene::PAGE_PAY_SCAN;
            $scene->payload = json_encode([
                'merchant_id' => $merchantId,
            ]);
            $scene->save();
        }
        try{
            $qrcode_url = WechatService::getMiniprogramAppCodeUrl($scene);

            $signboardName = MerchantService::getMerchantValueByIdAndKey($merchantId, 'signboard_name');
            $fileName = pathinfo($qrcode_url, PATHINFO_BASENAME);
            $path = storage_path('app/public/miniprogram/app_code/') . $fileName;
            WechatService::addNameToAppCode($path, $signboardName);
        }catch (\Exception $e){
            throw new BaseResponseException('小程序码生成失败');
        }

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
        $currentUser = request()->get('current_user');

        $scene = MiniprogramScene::where('type', MiniprogramScene::TYPE_PAY_SCAN)
            ->where('merchant_id', $currentUser->merchant_id)
            ->first();

        $width = $type == 3 ? 1280 : ($type == 2 ? 430 : 258);
        $filePath = MiniprogramSceneService::getMiniprogramAppCode($scene, $width, true);

        $signboardName = MerchantService::getMerchantValueByIdAndKey($currentUser->merchant_id, 'signboard_name');
        WechatService::addNameToAppCode($filePath, $signboardName);

        return response()->download($filePath, '支付小程序码_' . ['', '小', '中', '大'][$type] . '.jpg');
    }
}