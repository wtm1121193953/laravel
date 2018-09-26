<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/15
 * Time: 17:12
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Result;

class VersionController extends Controller
{

    /**
     * 获取最新版本
     */
    public function last()
    {
        $appType = request()->headers->get('app-type');
        return Result::success([
            'version' => 'v1.0.0',
            'force' => '1',
            'desc' => '更新说明',
            'app_type' => $appType,
        ]);
    }

    /**
     * 获取版本信息列表
     */
    public function getList()
    {
        $appType = request()->headers->get('app-type');
        return Result::success([
            'last' => [
                'version' => 'v1.0.2',
                'force' => '1',
                'desc' => '更新说明',
                'app_type' => $appType,
            ],
            'versions' => [
                [
                    'version' => 'v1.0.0',
                    'force' => '0',
                    'desc' => '更新说明',
                    'app_type' => $appType,
                ],
                [
                    'version' => 'v1.0.1',
                    'force' => '0',
                    'desc' => '更新说明',
                    'app_type' => $appType,
                ],
                [
                    'version' => 'v1.0.2',
                    'force' => '0',
                    'desc' => '更新说明',
                    'app_type' => $appType,
                ],
            ]
        ]);
    }
}