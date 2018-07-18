<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/18
 * Time: 10:47
 */

namespace App\Modules\Merchant;


use Pimple\Tests\Fixtures\Service;

class MerchantAuditService extends Service
{

    public static function addAudit($merchantId, $operId, $status = Merchant::AUDIT_STATUS_AUDITING)
    {
        // 需要增加一个取消审核状态, 将未审核的记录设为取消
        MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $operId)
            ->whereIn('status', [Merchant::AUDIT_STATUS_AUDITING, Merchant::AUDIT_STATUS_RESUBMIT])
            ->update(['status' => Merchant::AUDIT_STATUS_CANCEL]);
        $audit = new MerchantAudit();
        $audit->merchant_id = $merchantId;
        $audit->oper_id = $operId;
        $audit->status = $status;
        $audit->save();
        return $audit;
    }

    public static function cancelAudit()
    {
        // todo 取消审核
    }

    public static function auditSuccess()
    {
        // todo 审核通过
    }

    public static function auditFail()
    {
        // todo 审核不通过
    }

    public static function auditFailAndPushToPool()
    {
        // todo 审核不通过并且打回商户池
    }
}