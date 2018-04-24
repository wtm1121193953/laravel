<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Events\MerchantSaved;

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
    /**
     * 重新提交审核
     */
    const AUDIT_STATUS_RESUBMIT = 3;

    /**
     * 未签订合同
     */
    const CONTRACT_STATUS_YES = 1;
    /**
     * 已签订合同
     */
    const CONTRACT_STATUS_NO = 2;

    /**
     * 结算类型
     */
    const SETTLE_WEEKLY = 1; // 周结
    const SETTLE_HALF_MONTHLY = 2; // 半月结
    const SETTLE_MONTHLY = 3; // 月结
    const SETTLE_HALF_YEARLY = 4; // 半年结
    const SETTLE_YEARLY = 5; // 年结


}
