<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/18
 * Time: 10:47
 */

namespace App\Modules\Merchant;


use App\Modules\Oper\Oper;
use Pimple\Tests\Fixtures\Service;

class MerchantAuditService extends Service
{

    /**
     * 获取审核结果列表
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAuditResultList()
    {
        $data = MerchantAudit::whereIn('status', [
            Merchant::AUDIT_STATUS_SUCCESS,
            Merchant::AUDIT_STATUS_FAIL,
            Merchant::AUDIT_STATUS_FAIL_TO_POOL,
        ]) ->orderByDesc('updated_at')->paginate();

        $data->each(function($item) {
            $item->merchantName = Merchant::where('id', $item->merchant_id)->value('name');
            $item->operName = Oper::where('id', $item->oper_id)->value('name');
        });
        return $data;
    }

    /**
     * @param $merchantId
     * @return MerchantAudit
     */
    public static function getNewestAuditRecordByMerchantId($merchantId)
    {
        $merchant = Merchant::where('id', $merchantId)
            ->select('id', 'name', 'merchant_category_id')
            ->first();
        $record = MerchantAudit::where("merchant_id", $merchantId)
            ->whereNotIn('status', [Merchant::AUDIT_STATUS_AUDITING, Merchant::AUDIT_STATUS_CANCEL])
            ->orderByDesc('updated_at')
            ->first();

        $record->categoryName= MerchantCategory::where("id", $merchant->merchant_category_id)->value("name");
        $record->merchantName = $merchant->name;
        return $record;

    }

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