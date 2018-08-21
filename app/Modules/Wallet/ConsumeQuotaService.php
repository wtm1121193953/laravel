<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Modules\Order\Order;

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
        // todo
        // 1. 添加自己的消费额
        // 2. 添加上级的消费额

    }

    /**
     * 添加自己的消费额
     * @param Order $order
     */
    public static function addFreezeConsumeQuotaToSelf(Order $order)
    {
        // todo
        // 1. 添加冻结中的消费额
        // 2. 添加消费额记录
    }

    /**
     * 添加上级的消费额
     * @param Order $order
     */
    public static function addFreezeConsumeQuotaToParent(Order $order)
    {
        // todo
        // 1. 添加冻结中的消费额
        // 2. 添加消费额记录
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
}