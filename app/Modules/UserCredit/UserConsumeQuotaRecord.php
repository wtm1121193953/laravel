<?php

namespace App\Modules\UserCredit;

use Illuminate\Database\Eloquent\Model;

class UserConsumeQuotaRecord extends Model
{
    //
    const TYPE_TO_SELF = 1;
    const TYPE_TO_PARENT = 2;
}
