<?php

namespace App\Modules\FeeSplitting;


use App\BaseService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;

class FeeSplittingService extends BaseService
{

    /**
     * 根据订单执行分润
     * @param Order $order
     */
    public static function feeSplittingByOrder(Order $order)
    {
        // todo
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
        // todo
        FeeSplittingRecord::TYPE_TO_SELF;
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