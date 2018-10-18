<?php

namespace App\Modules\Platform;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlatformTradeRecord
 * @package App\Modules\Platform
 * @property number type
 * @property number pay_id
 * @property float trade_amount
 * @property Carbon trade_time
 * @property string trade_no
 * @property string order_no
 * @property number oper_id
 * @property number merchant_id
 * @property string remark
 */
class PlatformTradeRecord extends Model
{
    //
    const TYPE_PAY = 1;
    const TYPE_REFUND = 2;
}