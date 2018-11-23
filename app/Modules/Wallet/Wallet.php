<?php

namespace App\Modules\Wallet;

use App\BaseModel;
use App\Modules\User\GenPassword;

/**
 * Class Wallet
 * @package App\Modules\Wallet
 * @property integer origin_id
 * @property integer origin_type
 * @property float balance
 * @property float freeze_balance
 * @property float consume_quota
 * @property float freeze_consume_quota
 * @property float share_consume_quota
 * @property float share_freeze_consume_quota
 * @property string withdraw_password
 * @property string salt
 * @property integer status
 */

class Wallet extends BaseModel
{
    use GenPassword;
    /**
     * 钱包用户类型
     */
    const ORIGIN_TYPE_USER = 1; // 用户
    const ORIGIN_TYPE_MERCHANT = 2; // 商户
    const ORIGIN_TYPE_OPER = 3; // 运营中心
    const ORIGIN_TYPE_BIZER = 4; // 业务员
    const ORIGIN_TYPE_CS = 5; // 超市

    /**
     * 钱包状态 状态 1-正常 2-冻结
     */
    const STATUS_ON = 1;
    const STATUS_OFF = 2;
}
