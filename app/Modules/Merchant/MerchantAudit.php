<?php

namespace App\Modules\Merchant;

use App\BaseModel;

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
     * 重新提交审核
     * @param $merchantId
     * @param $operId
     * @return MerchantAudit
     */
    public static function resubmit($merchantId, $operId)
    {
        $audit = static::where('merchant_id', $merchantId)
            ->where('oper_id', $operId)
            ->first();
        if(!$audit){
            $audit = static::addRecord($merchantId, $operId);
        }else {
            $audit->status = Merchant::AUDIT_STATUS_RESUBMIT;
            $audit->save();
        }
        return $audit;
    }
}
