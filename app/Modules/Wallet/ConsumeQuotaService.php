<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserCreditSettingService;

/**
 * 消费额相关service
 * Class ConsumeQuotaService
 * @package App\Modules\Wallet
 */
class ConsumeQuotaService extends BaseService
{

    /**
     * 添加消费额
     * @param $order
     */
    public static function addFreezeConsumeQuota(Order $order)
    {
        // 1. 添加自己的消费额
        self::addFreezeConsumeQuotaToSelf($order);
        // 2. 添加上级的消费额
        self::addFreezeConsumeQuotaToParent($order);
    }

    /**
     * 添加自己的消费额
     * @param Order $order
     */
    public static function addFreezeConsumeQuotaToSelf(Order $order)
    {
        // 1. 添加冻结中的消费额
        $user = UserService::getUserById($order->user_id);
        $wallet = WalletService::getWalletInfo($user);
        $wallet->freeze_consume_quota = $wallet->freeze_consume_quota + $order->pay_price;
        $wallet->save();
        // 2. 添加消费额记录
        self::createWalletConsumeQuotaRecord($order, $wallet, WalletConsumeQuotaRecord::TYPE_SELF);
    }

    /**
     * 添加上级的消费额
     * @param Order $order
     */
    public static function addFreezeConsumeQuotaToParent(Order $order)
    {
        // 1. 添加冻结中的消费额
        $parent = InviteUserService::getParent($order->user_id);
        if ($parent == null) {
            return;
        }
        $consumeQuotaToParentRatio = UserCreditSettingService::getConsumeQuotaToParentRatio();
        $wallet = WalletService::getWalletInfo($parent);
        $wallet->freeze_consume_quota = $wallet->freeze_consume_quota + ($order->pay_price * $consumeQuotaToParentRatio / 100);
        $wallet->save();
        // 2. 添加消费额记录
        self::createWalletConsumeQuotaRecord($order, $wallet, WalletConsumeQuotaRecord::TYPE_SUBORDINATE);
    }

    /**
     * 解冻消费额
     * @param Order $order
     */
    public static function unfreezeConsumeQuota(Order $order)
    {
        // todo
        // 1. 解冻自己的消费额
        // 2. 解冻上级的消费额

        // 3. 发送同步消费额到tps的队列

    }

    /**
     * 退回消费额
     * @param Order $order
     */
    public static function refundConsumeQuota(Order $order)
    {
        // todo
        // 判断消费额是否可退回
        // 1. 退回自己的消费额
        // 2. 退回上级的消费额
    }

    /**
     * 同步消费额数据到tps, 需要按订单去同步
     * @param Order $order
     */
    public static function syncConsumeQuotaToTps(Order $order)
    {
        // todo
        //
    }

    /**
     * 创建消费额记录
     * @param Order $order
     * @param Wallet $wallet
     * @param $type
     * @return WalletConsumeQuotaRecord
     */
    private static function createWalletConsumeQuotaRecord(Order $order, Wallet $wallet, $type)
    {
        if ($type == WalletConsumeQuotaRecord::TYPE_SUBORDINATE) {
            $consumeQuotaToParentRatio = UserCreditSettingService::getConsumeQuotaToParentRatio();
            $consumeQuota = $order->pay_price * $consumeQuotaToParentRatio / 100;
        } else {
            $consumeQuota = $order->pay_price;
        }

        $consumeQuotaRecord = new WalletConsumeQuotaRecord();
        $consumeQuotaRecord->wallet_id = $wallet->id;
        $consumeQuotaRecord->origin_id = $wallet->origin_id;
        $consumeQuotaRecord->origin_type = $wallet->origin_type;
        $consumeQuotaRecord->type = $type;
        $consumeQuotaRecord->order_id = $order->id;
        $consumeQuotaRecord->order_no = $order->order_no;
        $consumeQuotaRecord->order_profit_amount = OrderService::getProfitAmount($order);
        $consumeQuotaRecord->consume_quota = $consumeQuota;
        $consumeQuotaRecord->consume_user_mobile = $order->notify_mobile;
        $consumeQuotaRecord->status = WalletConsumeQuotaRecord::STATUS_FREEZE;
        $consumeQuotaRecord->save();

        return $consumeQuotaRecord;
    }
}