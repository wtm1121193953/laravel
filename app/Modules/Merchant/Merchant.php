<?php

namespace App\Modules\Merchant;

use App\BaseModel;

class Merchant extends BaseModel
{
    //
    /**
     * 未审核(审核中)
     */
    const AUDIT_STATUS_AUDITING = 0;
    /**
     * 审核通过
     */
    const AUDIT_STATUS_SUCCESS = 1;
    /**
     * 审核不通过
     */
    const AUDIT_STATUS_FAIL = 2;

}
