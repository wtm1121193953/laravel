<?php

namespace App\Modules\Merchant;

use App\BaseModel;

/**
 * Class MerchantAudit
 * @package App\Modules\Merchant
 *
 * @property int    oper_id
 * @property int    merchant_id
 * @property string audit_suggestion
 * @property int    status
 *
 */
class MerchantAudit extends BaseModel
{
    // 状态 0-待审核 1-审核通过 2-审核不通过 3-重新提交审核 4-已被打回到商户池
    const STATUS_UN_AUDIT = 0;
    const STATUS_AUDIT_SUCCESS = 1;
    const STATUS_AUDIT_FAILED = 2;
    const STATUS_RE_AUDIT = 3;
    const STATUS_TO_POOL = 4;
}
