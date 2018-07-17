<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use Illuminate\Support\Carbon;

class MerchantAudit extends BaseModel
{
    //

    /**
     * 添加审核记录
     * @param $merchantId
     * @param $operId
     * @return MerchantAudit
     */
    public static function addRecord($merchantId, $operId)
    {
        $audit = new static();
        $audit->merchant_id = $merchantId;
        $audit->oper_id = $operId;
        $audit->status = Merchant::AUDIT_STATUS_AUDITING;
        $audit->save();
        return $audit;
    }

    /**
     * 重新提交审核生成新记录
     * @param $merchantId
     * @param $operId
     * @return MerchantAudit
     */
    public static function resubmit($merchantId, $operId)
    {
        $audit = new static();
        $audit->merchant_id = $merchantId;
        $audit->oper_id = $operId;
        $audit->status = Merchant::AUDIT_STATUS_RESUBMIT;
        $audit->save();
        return $audit;
    }





}
