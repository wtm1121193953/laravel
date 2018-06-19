<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30
 * Time: 15:17
 */

namespace App\Modules\UserCredit;


use App\Modules\Setting\SettingService;

class UserCreditSettingService extends SettingService
{

    /**
     * 获取直推用户累计额度换算系数
     */
    public static function getConsumeQuotaConvertRatioSetting()
    {
        return self::getValueByKey('consume_quota_convert_ratio_to_parent');
    }

    /**
     * 获取金额与积分转换比例
     */
    public static function getCreditMultiplierOfAmountSetting()
    {
        return self::getValueByKey('credit_multiplier_of_amount');
    }

    /**
     * 获取运营中心抽成比例配置
     */
    public static function getOperProfitRadioSetting()
    {
        return self::getValueByKey('oper_profit_radio');
    }

    /**
     * 根据用户积分数量获取用户等级
     * @param int $creditNumber
     * @return int
     */
    public static function getUserLevelByCreditNumber($creditNumber)
    {
        $userLevelSettings = self::get('user_level_1_of_credit_number',
            'user_level_2_of_credit_number',
            'user_level_3_of_credit_number',
            'user_level_4_of_credit_number');

        if($creditNumber >= $userLevelSettings['user_level_4_of_credit_number']){
            return 4;
        }else if($creditNumber >= $userLevelSettings['user_level_3_of_credit_number']){
            return 3;
        }else if($creditNumber >= $userLevelSettings['user_level_2_of_credit_number']) {
            return 2;
        }else {
            return 1;
        }
    }

    /**
     * 根据商户邀请的用户数量获取商户等级
     * @param int $inviteUserNumber
     * @return int
     */
    public static function getMerchantLevelByInviteUserNumber($inviteUserNumber)
    {
        $merchantLevelSettings = self::get('merchant_level_1_of_invite_user_number',
            'merchant_level_2_of_invite_user_number',
            'merchant_level_3_of_invite_user_number');

        if($inviteUserNumber >= $merchantLevelSettings['merchant_level_3_of_invite_user_number']){
            return 3;
        }else if($inviteUserNumber >= $merchantLevelSettings['merchant_level_2_of_invite_user_number']){
            return 2;
        }else {
            return 1;
        }
    }

    /**
     * 根据用户等级获取自返比例配置
     * @param $userLevel
     * @return string
     */
    public static function getCreditToSelfRatioSetting($userLevel)
    {
        $key = 'credit_to_self_ratio_of_user_level_'.$userLevel;
        $ratio = self::getValueByKey($key);
        return $ratio;
    }

    /**
     * 根据用户等级获取 分享提成 比例
     * @param $userLevel
     * @return string
     */
    public static function getCreditToParentRatioSetting($userLevel)
    {
        $key = 'credit_to_parent_ratio_of_user_level_'.$userLevel;
        $parentRatio = self::getValueByKey($key);
        return $parentRatio;
    }

    /**
     * 根据商户等级获取 商户等级加成 配置
     * @param $merchantLevel
     * @return string
     */
    public static function getCreditMultiplierOfMerchantSetting($merchantLevel)
    {
        $key = 'credit_multiplier_of_merchant_level_'.$merchantLevel;
        $creditMultiplier = self::getValueByKey($key);
        return $creditMultiplier;
    }

}