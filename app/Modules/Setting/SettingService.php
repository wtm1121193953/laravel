<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/18
 * Time: 13:55
 */

namespace App\Modules\Setting;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    const SETTING_DESCS = [
        'merchant_share_in_miniprogram' => '小程序端商户共享|不同运营中心的小程序共享用户间的商户与订单',
        'oper_profit_radio' => '运用中心抽成配置(百分比, 抽成金额 = 订单利润 * 抽成比例)',
        'consume_quota_convert_ratio_to_parent' => '直推用户累计额度换算系数(百分比)',
        'credit_multiplier_of_amount' => '积分系数配置(积分 = 返利金额 * 系数)',
        'user_level_1_of_credit_number' => '用户等级1(萌新)对应的积分数量',
        'user_level_2_of_credit_number' => '用户等级2(粉丝)对应的积分数量',
        'user_level_3_of_credit_number' => '用户等级3(铁杆)对应的积分数量',
        'user_level_4_of_credit_number' => '用户等级4(骨灰)对应的积分数量',
        'merchant_level_1_of_invite_user_number' => '商户等级1(签约商户)对应的邀请用户数量',
        'merchant_level_2_of_invite_user_number' => '商户等级2(联盟商户)对应的邀请用户数量',
        'merchant_level_3_of_invite_user_number' => '商户等级3(品牌商户)对应的邀请用户数量',
        'credit_to_self_ratio_of_user_level_1' => '自返比例: 用户等级1(萌新), 百分比',
        'credit_to_self_ratio_of_user_level_2' => '自返比例: 用户等级2(粉丝), 百分比',
        'credit_to_self_ratio_of_user_level_3' => '自返比例: 用户等级3(铁杆), 百分比',
        'credit_to_self_ratio_of_user_level_4' => '自返比例: 用户等级4(骨灰), 百分比',
        'credit_to_parent_ratio_of_user_level_2' => '分享提成: 用户等级2(粉丝), 百分比',
        'credit_to_parent_ratio_of_user_level_3' => '分享提成: 用户等级3(铁杆), 百分比',
        'credit_to_parent_ratio_of_user_level_4' => '分享提成: 用户等级4(骨灰), 百分比',
        'credit_multiplier_of_merchant_level_1' => '商户等级加成: 商户等级1(签约商家), 倍数',
        'credit_multiplier_of_merchant_level_2' => '商户等级加成: 商户等级2(联盟商户), 倍数',
        'credit_multiplier_of_merchant_level_3' => '商户等级加成: 商户等级2(品牌商户), 倍数',

        // 分润相关
        'fee_splitting_ratio_to_self' => '分润: 自返比例',
        'fee_splitting_ratio_to_parent_of_user' => '分润: 返上级比例(用户)',
        'fee_splitting_ratio_to_parent_of_merchant_level_1' => '分润: 返上级比例(商户等级1)',
        'fee_splitting_ratio_to_parent_of_merchant_level_2' => '分润: 返上级比例(商户等级2)',
        'fee_splitting_ratio_to_parent_of_merchant_level_3' => '分润: 返上级比例(商户等级3)',
        'fee_splitting_ratio_to_parent_of_oper' => '分润: 返上级比例(运营中心)',

        // 商户端电子合同开关
        'merchant_electronic_contract' => '商户端电子合同开关|商户端是否显示电子合同的签约和详情页',
    ];

    /**
     * 获取配置说明详情
     * @param $key
     * @return mixed|string
     */
    protected static function getDesc($key){
        return self::SETTING_DESCS[$key] ?? '';
    }

    /**
     * 设置配置
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $setting = Setting::where('key', $key)->first();
        if(empty($setting)){
            $setting = new Setting();
            $setting->key = $key;
            $info = explode('|', self::getDesc($key));
            $name = $info[0];
            $desc = $info[1] ?? $info[0];
            $setting->name = $name;
            $setting->desc = $desc;
        }
        $setting->value = $value;
        $setting->save();
        Cache::forget('setting_cache');
        Cache::forget('setting_cache:' . $key);
    }

    /**
     * 根据key获取配置值
     * @param $key
     * @return string
     */
    public static function getValueByKey($key)
    {
        $value = Cache::get('setting_cache:' . $key);
        if(empty($value)){
            $value = Setting::where('key', $key)->value('value');
            Cache::forever('setting_cache:' . $key, $value);
        }
        $value =  $value ?? '';
        $intval_list = [
            'supermarket_on',
            'supermarket_city_limit',
            'supermarket_show_city_limit',
            'supermarket_index_cs_banner_on'
        ];

        //需要整形处理的
        if (in_array($key,$intval_list)) {
            return intval($value);
        } else {
            return $value;
        }
    }

    /**
     * 根据 key 获取
     * @param array $keys
     * @return Collection
     */
    public static function get(...$keys)
    {
        $settings = self::getAll();
        if (empty($keys)){
            return $settings;
        }
        return $settings->only($keys);
    }

    /**
     * 获取全部系统设置项
     * @return Collection
     */
    protected static function getAll(){
        $settings = Cache::get('setting_cache');
        if(empty($settings)){
            $settings = collect();
            Setting::all()->each(function($item) use ($settings){
                $settings->put($item->key, $item->value);
            });
            Cache::forever('setting_cache', $settings);
        }
        return $settings;
    }
}