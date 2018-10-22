<?php

namespace App\Modules\Platform;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PlatformTradeRecordsDaily
 * @package App\Modules\Platform
 *
 * @property Carbon sum_date
 * @property integer pay_id
 * @property float pay_amount
 * @property integer pay_count
 * @property float refund_amount
 * @property integer refund_count
 */
class PlatformTradeRecordsDaily extends Model
{
    //
    protected $guarded = [];
}
