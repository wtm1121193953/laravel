<?php

namespace App\Modules\Email;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * Class EmailVerifyCode
 * @package App\Modules\Email
 *
 * @property int    type
 * @property int status
 * @property string email
 * @property string verify_code
 * @property Carbon expire_time
 */
class EmailVerifyCode extends BaseModel
{
    //
}
