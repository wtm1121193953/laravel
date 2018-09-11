<?php

namespace App\Modules\Wallet;

use App\BaseModel;

/**
 * Class WalletBatch
 * @package App\Modules\Wallet
 * @property string batch_no
 * @property integer type
 * @property integer status
 * @property float amount
 * @property integer total
 * @property float success_amount
 * @property integer success_total
 * @property float failed_amount
 * @property integer failed_total
 * @property string remark
 */

class WalletBatch extends BaseModel
{
    /**
     * 批次类型 1-对公 2-对私
     */
    const TYPE_PUBLIC = 1;
    const TYPE_PRIVATE = 2;

    /**
     * 批次状态 1-结算中 2-准备打款 3-打款完成
     */
    const STATUS_SETTLEMENT = 1;
    const STATUS_PREPARE_WITHDRAW = 2;
    const STATUS_WITHDRAW_SUCCESS = 3;
}
