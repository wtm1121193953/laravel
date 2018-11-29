<?php

namespace App\Modules\Platform;

use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class PlatformTradeRecord
 * @package App\Modules\Platform
 * @property number merchant_type
 * @property number type
 * @property number pay_id
 * @property float trade_amount
 * @property Carbon trade_time
 * @property string trade_no
 * @property string order_no
 * @property number oper_id
 * @property number merchant_id
 * @property number user_id
 * @property string remark
 */
class PlatformTradeRecord extends Model
{
    //
    const TYPE_PAY = 1;
    const TYPE_REFUND = 2;

    // 商户类型
    const MERCHANT_TYPE_NORMAL = 1;         // 默认商家
    const MERCHANT_TYPE_SUPERMARKET = 2;    // 超市商家

    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getAllType()
    {
        return [self::TYPE_PAY=>'支付',self::TYPE_REFUND=>'退款'];
    }
}