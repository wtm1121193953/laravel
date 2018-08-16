<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\WechatService;

class PayQrcodeService extends BaseService
{
    public static function getMiniprogramAppCode($merchantId,$operId){

        $scene = self::downloadMiniprogramAppCode($merchantId);

        if(empty($scene)){
            $scene = new MiniprogramScene();
            $scene->$operId;
            $scene->merchant_id = $merchantId;
            $scene->type = MiniprogramScene::TYPE_PAY_SCAN;
            $scene->page = MiniprogramScene::PAGE_PAY_SCAN;
            $scene->payload = json_encode([
                'merchant_id' => $merchantId,
            ]);
            $scene->save();
        }
        try{
            if (empty($scene->qrcode_url)) {
                $qrcode_url = WechatService::getMiniprogramAppCodeUrl($scene);

                $signboardName = MerchantService::getMerchantValueByIdAndKey($merchantId, 'signboard_name');
                $fileName = pathinfo($qrcode_url, PATHINFO_BASENAME);
                $path = storage_path('app/public/miniprogram/app_code/') . $fileName;
                WechatService::addNameToAppCode($path, $signboardName);
            } else {
                $qrcode_url = $scene->qrcode_url;
            }

        }catch (\Exception $e){
            throw new BaseResponseException('小程序码生成失败');
        }

        return $qrcode_url;
    }

    public static function downloadMiniprogramAppCode($merchantId){

        $scene = MiniprogramScene::where('type', MiniprogramScene::TYPE_PAY_SCAN)
            ->where('merchant_id', $merchantId)
            ->first();
        return $scene;
    }

}
