<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18
 * Time: 13:07
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Setting\Setting;
use App\Modules\Setting\SettingService;
use App\Result;

class SettingController extends Controller
{

    /**
     * 系统配置详情
     */
    public function getList()
    {
        $settings = SettingService::get('merchant_share_in_miniprogram');
        return Result::success([
            'list' => $settings
        ]);
    }

    public function edit(){
        $data = request()->all([
            'merchant_share_in_miniprogram'
        ]);
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }
}