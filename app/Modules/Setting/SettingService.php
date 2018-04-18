<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18
 * Time: 13:55
 */

namespace App\Modules\Setting;


class SettingService
{

    /**
     * 根据key获取配置值
     * @param $key
     * @return mixed|string
     */
    public static function getValueByKey($key)
    {
        $value = Setting::where('key', $key)->value('value');
        return $value ?? '';
    }
}