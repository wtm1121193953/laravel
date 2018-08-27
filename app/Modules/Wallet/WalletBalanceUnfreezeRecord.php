<?php

namespace App\Modules\Wallet;

use App\BaseModel;

/**
 * Class WalletBalanceUnfreezeRecord
 * @package App\Modules\Wallet
 * @property integer wallet_id
 * @property integer origin_id
 * @property integer origin_type
 * @property integer fee_splitting_record_id
 * @property float unfreeze_amount
 */

class WalletBalanceUnfreezeRecord extends BaseModel
{
    /**
     * 用户类型 1-用户 2-商户 3-运营中心
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心
}
