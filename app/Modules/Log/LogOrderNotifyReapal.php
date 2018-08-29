<?php

namespace App\Modules\Log;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogOrderNotifyReapal
 * @package App\Modules\Log
 *
 * @property
 */
class LogOrderNotifyReapal extends Model
{
    //
    const TYPE_PAY = 1;
    const TYPE_REFUND = 2;
}
