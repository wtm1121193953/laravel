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
     * 小程序端商户共享系统配置详情
     */
    public function getList()
    {
        $settings = SettingService::get('merchant_share_in_miniprogram');
        return Result::success([
            'list' => $settings
        ]);
    }

    /**
     * 小程序端商户共享保存
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(){
        $data = request()->all([
            'merchant_share_in_miniprogram'
        ]);
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }

    /**
     * 获取积分规则配置列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCreditRulesList()
    {
        $list = SettingService::exceptKeys('merchant_share_in_miniprogram');
        return Result::success([
            'list' => $list
        ]);
    }

    /**
     * 保存积分规则列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setCreditRules()
    {
        $data = request()->all();
        foreach ($data as $key => $value) {
            SettingService::set($key, $value);
        }
        return Result::success();
    }
}