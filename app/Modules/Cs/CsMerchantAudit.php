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
 */
class CsMerchantAudit extends BaseModel
{
    //
    const INSERT_TYPE = 1;
    const UPDATE_TYPE= 2;

    const ING_AUDIT = 1;
    const PASS_AUDIT= 2;
    const NO_AUDIT = 3;
}
