<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;

class WalletService extends BaseService
{

    /**
     * 创建钱包
     * @param User|Merchant|Oper $user
     * @return Wallet
     */
    private static function createWallet($user)
    {
        return null;
    }

    /**
     * 增加余额
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function addFreezeBalance(FeeSplittingRecord $feeSplittingRecord)
    {
        // todo
        // 1.添加冻结金额
        // 2.添加钱包流水
    }

    /**
     * 解冻金额
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function unfreezeBalance(FeeSplittingRecord $feeSplittingRecord)
    {
        // todo
        // 1. 解冻金额
        // 2. 添加解冻记录
    }

    /**
     * 分润退款
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function refundBalance(FeeSplittingRecord $feeSplittingRecord)
    {
        // todo
        // 1. 判断状态, 若已解冻, 则不能退回
        // 2. 退回冻结金额
        // 3. 添加钱包流水
    }
}