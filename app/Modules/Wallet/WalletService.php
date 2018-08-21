<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/21
 * Time: 12:00
 */

namespace App\Modules\Wallet;


use App\BaseService;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;

class WalletService extends BaseService
{

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