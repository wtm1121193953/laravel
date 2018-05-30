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
     */
    public static function getUserLevelByCreditNumber($creditNumber)
    {
        // todo
    }

    /**
     * 根据商户邀请的用户数量获取商户等级
     * @param int $inviteUserNumber
     */
    public static function getMerchantLevelByInviteUserNumber($inviteUserNumber)
    {
        // todo
    }

    /**
     * 根据用户等级获取自返比例配置
     * @param $userLevel
     */
    public static function getCreditToSelfRatioSetting($userLevel)
    {
        // todo
    }

    /**
     * 根据用户等级获取 分享提成 比例
     * @param $userLevel
     */
    public static function getCreditToParentRatioSetting($userLevel)
    {
        // todo
    }

    /**
     * 根据商户等级获取 商户等级加成 配置
     * @param $merchantLevel
     */
    public static function getCreditMultiplierOfMerchantSetting($merchantLevel)
    {
        // todo
    }

}