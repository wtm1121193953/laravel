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
        // 需要增加一个取消审核状态, 将未审核的记录设为取消
        MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $operId)
            ->whereIn('status', [Merchant::AUDIT_STATUS_AUDITING, Merchant::AUDIT_STATUS_RESUBMIT])
            ->update(['status' => Merchant::AUDIT_STATUS_CANCEL]);
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
        // 需要增加一个取消审核状态, 将未审核的记录设为取消
        MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $operId)
            ->whereIn('status', [Merchant::AUDIT_STATUS_AUDITING, Merchant::AUDIT_STATUS_RESUBMIT])
            ->update(['status' => Merchant::AUDIT_STATUS_CANCEL]);
        $audit = new static();
        $audit->merchant_id = $merchantId;
        $audit->oper_id = $operId;
        $audit->status = Merchant::AUDIT_STATUS_RESUBMIT;
        $audit->save();
        return $audit;
    }





}
