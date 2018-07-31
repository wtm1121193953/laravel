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
use App\Modules\Wechat\MiniprogramScene;
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
        $inviteQrcodeFilename = WechatService::genMiniprogramAppCode($currentUser->oper_id, $scene->id, $scene->page, $width, true);
        $filename = storage_path('app/public/miniprogram/app_code') . '/' . $inviteQrcodeFilename;
        return response()->download($filename, '支付小程序码_' . ['', '小', '中', '大'][$type] . '.jpg');
    }
}