<?php

namespace App\Modules\UserCredit;

use App\BaseModel;

/**
 * Class UserCreditRecord
 * @package App\Modules\UserCredit
 *
 * @property int    user_id
 * @property int    inout_type
 * @property int    type
 * @property string order_no
 * @property string consume_user_mobile
 * @property number consume_quota
 *
 */

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
