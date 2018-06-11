<?php

namespace App\Modules\UserCredit;

use App\BaseModel;

class UserCreditRecord extends BaseModel
{
    const TYPE_FROM_SELF = 1; // 自反
    const TYPE_FROM_SHARE_SUB = 2; // 分享提成
    const TYPE_FROM_MERCHANT_SHARE = 3; // 分享提成
    const TYPE_REFUND = 4; // 退款退回
    const TYPE_BUY = 5; // 消费支出
    //
}
