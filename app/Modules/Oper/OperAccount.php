<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Modules\User\GenPassword;

/**
 * Class OperAccount
 * @package App\Modules\Oper
 *
 * @property number oper_id
 * @property string name
 * @property string account
 * @property string mobile
 * @property string email
 * @property string password
 * @property string salt
 * @property number status
 */

class OperAccount extends BaseModel
{
    use GenPassword;
    //
}
