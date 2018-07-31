<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 16:42
 */

namespace App\Modules\Merchant;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MerchantSettingService
{
    const info = [
        'dishes_enabled' => '单品购买功能设置'
    ];

    const defaultSettings = [
        'dishes_enabled' => 0
    ];

    public static function set($operId, $merchantId, $key, $value)
    {
        // set
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

        Cache::forget('merchant_setting_cache:' . $merchantId);
        Cache::forget("merchant_setting_cache:{$merchantId}:$key");
    }

    public static function getValueByKey($merchantId, $key)
    {
        $cacheKey = "merchant_setting_cache:{$merchantId}:$key";
        $value = Cache::get($cacheKey);
        if(empty($value)){
            $value = MerchantSetting::where('merchant_id', $merchantId)
                ->where('key', $key)
                ->value('value');
            Cache::put($cacheKey, $value, 30);
        }
        return $value ?? '';
    }


    /**
     * 根据 key 获取
     * @param $merchantId
     * @param array $keys
     * @return Collection
     */
    public static function get($merchantId, ...$keys)
    {
        $settings = self::getAll($merchantId);
        if (empty($keys)){
            return $settings;
        }
        return $settings->only(...$keys);
    }

    /**
     * 获取全部系统设置项
     * @param $merchantId
     * @return Collection
     */
    public static function getAll($merchantId){
        $cacheKey = 'merchant_setting_cache:' . $merchantId;
        $settings = Cache::get($cacheKey);
        if(empty($settings)){
            $settings = collect(self::defaultSettings);
            MerchantSetting::where('merchant_id', $merchantId)->get()->each(function($item) use ($settings){
                $settings->put($item->key, $item->value);
            });
            Cache::forever($cacheKey, $settings);
        }
        return $settings;
    }
}