<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 23:25
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Wechat\WechatService;

class MiniprogramController extends Controller
{

    /**
     * 生成小程序码
     */
    public function genAppCode()
    {

        $app = WechatService::getWechatMiniAppForOper(3);
        $response = $app->app_code->getUnlimit('id=52', [
            'page' => 'pages/severs/index/index',
            'width' => 300,
        ]);
        $filename = $response->saveAs(storage_path('app/public/miniprogram/app_code'), "3_52.png");
    }
}