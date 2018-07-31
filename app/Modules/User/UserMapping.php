<?php

namespace App\Modules\User;

use App\BaseModel;

/**
 * Class UserMapping
 * @package App\Modules\User
 *
 * @property int    origin_id
 * @property int    origin_type
 * @property int    user_id
 *
 */
class UserMapping extends BaseModel
{
    //
    const ORIGIN_TYPE_MERCHANT = 1;
    const ORIGIN_TYPE_OPER = 2;
}
