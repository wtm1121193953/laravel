<?php

namespace App\Modules\Oper;

use App\BaseModel;

/**
 * Class OperBizerLog
 * @package App\Modules\Oper
 * @property integer oper_id
 * @property integer bizer_id
 * @property string note
 * @property integer status
 * @property string apply_time
 * @property string remark
 */

class OperBizerLog extends BaseModel
{
    /**
     * 状态 0申请中, 1-签约 -1-拒绝
     */
    const STATUS_APPLYING = 0;
    const STATUS_SIGNED = 1;
    const STATUS_REJECTED = -1;

}
