<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 16:42
 */

namespace App\Modules\Merchant;


use Illuminate\Support\Facades\Cache;

class MerchantSettingService
{
    const info = [
        'dishes_function' => '单品购买功能设置'
    ];

    public static function set($operId, $merchantId, $key, $value)
    {
        // set
        Cache::forget("merchant_setting_key_{$merchantId}_$key");
        $setting = MerchantSetting::where('merchant_id', $merchantId)
            ->where('key', $key)
            ->first();
        if (empty($setting)){
            $setting = new MerchantSetting();
            $setting->oper_id = $operId;
            $setting->merchant_id = $merchantId;
            $setting->key = $key;
            $setting->info = self::info[$key];
        }
        $setting->value = $value;
        $setting->save();
    }

    public static function getValueByKey($merchantId, $key)
    {
        $cacheKey = "merchant_setting_key_{$merchantId}_$key";
        $value = Cache::get($cacheKey);
        if(empty($value)){
            $value = MerchantSetting::where('merchant_id', $merchantId)
                ->where('key', $key)
                ->value('value');
            Cache::put($cacheKey, $value, 30);
        }
        return $value ?? '';
    }
}