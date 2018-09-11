<?php

namespace App\Modules\Log;

use App\BaseModel;

class LogReapalPayRequest extends BaseModel
{
    //
    const TYPE_PAY = 1;
    const TYPE_REFUND = 2;
    const TYPE_AGENT_PAY = 3;
    const TYPE_AGENT_PAY_REFUND = 4;
}
