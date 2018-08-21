<?php

namespace App\Modules\FeeSplitting;


use App\BaseService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;

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
        $feeSplittingRecord = new FeeSplittingRecord();
        $feeSplittingRecord->origin_id = $order->user_id;
        $feeSplittingRecord->origin_type = FeeSplittingRecord::ORIGIN_TYPE_USER;
        $feeSplittingRecord->merchant_level = 0;
        $feeSplittingRecord->order_id = $order->id;
        $feeSplittingRecord->order_no = $order->order_no;
        $feeSplittingRecord->order_profit_amount = $profitAmount;
        $feeMultiplier = UserCreditSettingService::getFeeSplittingRatioToParentOfUserSetting(); // 自反的分润比例
        $feeSplittingRecord->ratio = $feeMultiplier;
        $feeSplittingRecord->amount = $profitAmount * $feeMultiplier;
        $feeSplittingRecord->type = FeeSplittingRecord::TYPE_TO_SELF;
        $feeSplittingRecord->status = FeeSplittingRecord::STATUS_FREEZE;
        $feeSplittingRecord->save();

        // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
        $wallet = Wallet::where('origin_id', $order->user_id)
            ->where('origin_type', Wallet::ORIGIN_TYPE_USER)
            ->first();
        if (empty($wallet)) {
            $wallet = new Wallet();
            $wallet->origin_id = $order->user_id;
            $wallet->origin_type = Wallet::ORIGIN_TYPE_USER;
            $wallet->save();
        }
        $wallet->freeze_balance = $wallet->freeze_balance + $feeSplittingRecord->amount;  // 更新钱包的冻结金额
        $wallet->save();

        // 钱包流水表 添加钱包流水记录
        $walletBill = new WalletBill();
        $walletBill->wallet_id = $wallet->id;
        $walletBill->origin_id = $order->user_id;
        $walletBill->origin_type = WalletBill::ORIGIN_TYPE_USER;
        $walletBill->bill_no = 0;

    }

    /**
     * 返利给上级逻辑
     * @param Order $order
     * @param float $profitAmount
     */
    private static function feeSplittingToParent(Order $order, float $profitAmount)
    {
        // todo
        FeeSplittingRecord::TYPE_TO_PARENT;
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
}