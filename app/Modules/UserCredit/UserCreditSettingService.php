<?php

namespace App\Modules\UserCredit;


use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
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
     * 获取 分润: 自返比例
     * @return string
     */
    public static function getFeeSplittingRatioToSelfSetting()
    {
        return self::getValueByKey('fee_splitting_ratio_to_self');
    }

    /**
     * 获取 分润: 返上级比例(用户)
     * @return string
     */
    public static function getFeeSplittingRatioToParentOfUserSetting()
    {
        return self::getValueByKey('fee_splitting_ratio_to_parent_of_user');
    }

    /**
     * 获取 分润: 返上级比例(运营中心)
     * @return string
     */
    public static function getFeeSplittingRatioToParentOfOperSetting()
    {
        return self::getValueByKey('fee_splitting_ratio_to_parent_of_oper');
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

    /**
     * 根据商户等级获取 分润: 返上级比例(商户等级)
     * @param $merchantLevel
     * @return string
     */
    public static function getFeeSplittingRatioToParentOfMerchantSetting($merchantLevel)
    {
        $key = 'fee_splitting_ratio_to_parent_of_merchant_level_'.$merchantLevel;
        $feeMultiplier = self::getValueByKey($key);
        return $feeMultiplier;
    }

    /**
     * 获取运营中心的分润比例
     * @param Oper $oper
     * @return int
     */
    public static function getFeeSplittingRatioToOper(Oper $oper)
    {
        if ($oper->pay_to_platform == Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING) {
            return 100;
        } elseif ($oper->pay_to_platform == Oper::PAY_TO_PLATFORM_WITH_SPLITTING) {
            return 50;
        } else {
            return null;
        }
    }

    /**
     * 获取返给上级的消费额比例
     * @return int
     */
    public static function getConsumeQuotaToParentRatio()
    {
        // todo
        return 50;
    }

    /**
     * 根据银行卡类型 获取商户取现的手续费百分比
     * @param $type
     * @return float|int
     */
    public static function getMerchantWithdrawChargeRatioByBankCardType($type)
    {
        if ($type == Merchant::BANK_CARD_TYPE_COMPANY) {
            $ratio = 0.2;
        } else {
            $ratio = 7;
        }
        return $ratio;
    }

    /**
     * 获取运营中心提现手续费百分比
     * @return float
     */
    public static function getOperWithdrawChargeRatio()
    {
        return 0.2;
    }

    /**
     * 获取用户提现手续费百分比
     * Author：  Jerry
     * Date：    180901
     * @return float
     */
    public static function getUserWithdrawChargeRatio()
    {
        return 7;
    }

    /**
     * 获取业务员提现手续费百分比
     * @return int
     */
    public static function getBizerWithdrawChargeRatio()
    {
        return 7;
    }

    /**
     * 获取 超市订单 分润给上级的分润比例%
     * @return int
     */
    public static function getFeeSplittingRatioToParentOfCs()
    {
        return 20;
    }

    /**
     * 获取 超市订单 分润给运营中心的分润比例%
     * @return int
     */
    public static function getFeeSplittingRatioToOperOfCs()
    {
        return 40;
    }

}