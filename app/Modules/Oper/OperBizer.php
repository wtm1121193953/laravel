<?php

namespace App\Modules\Oper;

use App\BaseModel;
use Carbon\Carbon;

/**
 * Class OperBizer
 * @package App\Modules\Oper
 * @property integer oper_id
 * @property integer bizer_id
 * @property Carbon sign_time
 * @property integer sign_status
 * @property float divide
 * @property string remark
 * @property string note
 * @property integer status
 * @property integer is_tips
 */

class OperBizer extends BaseModel {

    /**
     * 状态 0申请中, 1-签约 -1-拒绝
     */
    const STATUS_APPLYING = 0;
    const STATUS_SIGNED = 1;
    const STATUS_REJECTED = -1;

    /**
     * 签约状态，1-正常，0-冻结
     */
    const SIGN_STATUS_ON = 1;
    const SIGN_STATUS_OFF = 0;
}
