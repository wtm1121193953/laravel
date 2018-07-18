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
        // todo 创建审核记录
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