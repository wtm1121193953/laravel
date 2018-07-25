<?php

namespace App\Modules\UserCredit;

use App\BaseModel;

/**
 * Class UserCreditRecord
 * @package App\Modules\UserCredit
 *
 * @property int    user_id
 * @property int    credit
 * @property int    inout_type
 * @property int   type
 * @property int user_level
 * @property int merchant_level
 * @property string order_no
 * @property string consume_user_mobile
 * @property number order_profit_amount
 * @property number ratio
 * @property number credit_multiplier_of_amount
 *
 */
class UserCreditRecord extends BaseModel
{
    const TYPE_FROM_SELF = 1; // 自反
    const TYPE_FROM_SHARE_SUB = 2; // 分享提成
    const TYPE_FROM_MERCHANT_SHARE = 3; // 分享提成
    const TYPE_REFUND = 4; // 退款退回
    const TYPE_BUY = 5; // 消费支出

    //收支类型
    const INOUT_TYPE_IN = 1;
    const INOUT_TYPE_OUT = 2;
}
