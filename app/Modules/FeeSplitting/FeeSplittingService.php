<?php

namespace App\Modules\FeeSplitting;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;

class FeeSplittingService extends BaseService
{

    /**
     * 根据订单执行分润
     * @param Order $order
     */
    public static function feeSplittingByOrder(Order $order)
    {
        // 获取订单利润
        $profitAmount = OrderService::getProfitAmount($order);
        // 1 分给自己 5%
        self::feeSplittingToSelf($order, $profitAmount);
        // 2 分给上级 25%
        self::feeSplittingToParent($order, $profitAmount);
        // 3 分给运营中心  50% || 100% , 暂时不做
    }

    /**
     * 自返逻辑
     * @param Order $order
     * @param float $profitAmount
     */
    private static function feeSplittingToSelf(Order $order, float $profitAmount)
    {
        // 分润记录表 添加分润记录
        $originInfo = UserService::getUserById($order->user_id);
        $feeSplittingRecord = self::createFeeSplittingRecord($order, $originInfo, FeeSplittingRecord::TYPE_TO_SELF, $profitAmount);

        // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
        $wallet = WalletService::getWalletInfo($originInfo);

        WalletService::addFreezeBalance($feeSplittingRecord, $wallet);
    }

    /**
     * 返利给上级逻辑
     * @param Order $order
     * @param float $profitAmount
     */
    private static function feeSplittingToParent(Order $order, float $profitAmount)
    {
        $parent = InviteUserService::getParent($order->user_id);
        if ($parent == null) {
            return;
        }
        $feeSplittingRecord = self::createFeeSplittingRecord($order, $parent, FeeSplittingRecord::TYPE_TO_PARENT, $profitAmount);

        // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
        $wallet = WalletService::getWalletInfo($parent);

        WalletService::addFreezeBalance($feeSplittingRecord, $wallet);
    }

    /**
     * 根据订单解冻分润金额
     * @param $order
     */
    public static function unfreezeSplittingByOrder($order)
    {
        // todo
    }

    /**
     * 根据订单退回分润金额
     * @param $order
     */
    public static function refundSplittingByOrder($order)
    {
        // todo
        // 判断如果已解冻, 则不能退回
    }

    /**
     * 创建分润记录
     * @param Order $order
     * @param User|Merchant|Oper $originInfo
     * @param float $profitAmount
     * @param $type
     * @return FeeSplittingRecord
     */
    private static function createFeeSplittingRecord(Order $order, $originInfo, $type, float $profitAmount)
    {
        $merchantLevel = 0;
        if($originInfo instanceof User){
            $originType = FeeSplittingRecord::ORIGIN_TYPE_USER;
        }else if ($originInfo instanceof Merchant) {
            $originType = FeeSplittingRecord::ORIGIN_TYPE_MERCHANT;
        }else if($originInfo instanceof Oper){
            $originType = FeeSplittingRecord::ORIGIN_TYPE_OPER;
        }else {
            throw new BaseResponseException('用户类型错误');
        }
        if ($type == FeeSplittingRecord::TYPE_TO_SELF) {
            $feeRatio = UserCreditSettingService::getFeeSplittingRatioToSelfSetting(); // 自反的分润比例
        } elseif ($type == FeeSplittingRecord::TYPE_TO_PARENT) {
            if($originType == FeeSplittingRecord::ORIGIN_TYPE_USER){
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfUserSetting();
            }else if($originType == FeeSplittingRecord::ORIGIN_TYPE_MERCHANT){
                $merchantLevel = $originInfo->level;
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfMerchantSetting($merchantLevel);
            }else if($originType == FeeSplittingRecord::ORIGIN_TYPE_OPER) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfOperSetting();
            }else {
                throw new BaseResponseException();
            }
        } elseif ($type == FeeSplittingRecord::TYPE_TO_OPER) {
            $feeRatio = UserCreditSettingService::getFeeSplittingRatioToOper();
        } else {
            throw new ParamInvalidException('分润类型错误');
        }

        $feeSplittingRecord = new FeeSplittingRecord();
        $feeSplittingRecord->origin_id = $originInfo->id;
        $feeSplittingRecord->origin_type = $originType;
        $feeSplittingRecord->merchant_level = $merchantLevel ?: 0;
        $feeSplittingRecord->order_id = $order->id;
        $feeSplittingRecord->order_no = $order->order_no;
        $feeSplittingRecord->order_profit_amount = $profitAmount;
        $feeSplittingRecord->ratio = $feeRatio;
        $feeSplittingRecord->amount = $profitAmount * $feeRatio / 100;
        $feeSplittingRecord->type = $type;
        $feeSplittingRecord->status = FeeSplittingRecord::STATUS_FREEZE;
        $feeSplittingRecord->save();
        return $feeSplittingRecord;
    }
}