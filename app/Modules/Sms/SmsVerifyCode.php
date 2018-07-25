<?php

namespace App\Modules\Sms;

use App\BaseModel;
use Carbon\Carbon;


/**
 * Class SmsVerifyCode
 * @package App\Modules\Sms
 *
 * @property int    type
 * @property int status
 * @property string mobile
 * @property string verify_code
 * @property Carbon expire_time
 *
 */
class SmsVerifyCode extends BaseModel
{
    //
}
