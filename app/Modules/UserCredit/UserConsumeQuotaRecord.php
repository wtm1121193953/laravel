<?php

namespace App\Modules\UserCredit;

use App\BaseModel;

class UserConsumeQuotaRecord extends BaseModel
{
    //来源类型
    const TYPE_TO_SELF = 1;
    const TYPE_TO_PARENT = 2;
    const TYPE_TO_REFUND = 3;

    //收支类型
    const IN_TYPE = 1;
    const OUT_TYPE = 2;
}
