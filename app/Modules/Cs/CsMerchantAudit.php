<?php

namespace App\Modules\Cs;

use App\BaseModel;

/**
 * @property int type
 * @property string data_before
 * @property  string data_after
 * @property  string data_modify
 * @property  int status
 * @property int oper_id
 * @property string name
 * @property string suggestion
 * @property  int cs_merchant_id
 */
class CsMerchantAudit extends BaseModel
{
    //
    const INSERT_TYPE = 1;
    const UPDATE_TYPE= 2;

    /**
     * 未审核(审核中)
     */
    const AUDIT_STATUS_AUDITING = 1;
    /**
     * 审核通过
     */
    const AUDIT_STATUS_SUCCESS = 2;
    /**
     * 审核不通过
     */
    const AUDIT_STATUS_FAIL = 3;

    /**
     * 撤回
     */
    const AUDIT_STATUS_RECALL = 4;
}
