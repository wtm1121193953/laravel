<?php

namespace App\Modules\Merchant;

use App\Exceptions\ParamInvalidException;
use App\Modules\Oper\MyOperBizer;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Pimple\Tests\Fixtures\Service;

class MerchantAuditService extends Service
{

    /**
     * 获取审核结果列表
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAuditResultList(array $params = [])
    {

        $data = MerchantAudit::when(isset($params['oper_id']), function (Builder $query) use ($params){
            $query->where('oper_id', $params['oper_id']);
        })
            ->whereIn('status', [
                Merchant::AUDIT_STATUS_SUCCESS,
                Merchant::AUDIT_STATUS_FAIL,
                Merchant::AUDIT_STATUS_FAIL_TO_POOL,
            ])
            ->orderByDesc('updated_at')->paginate();

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
            ->whereIn('status', [
                Merchant::AUDIT_STATUS_SUCCESS,
                Merchant::AUDIT_STATUS_FAIL,
                Merchant::AUDIT_STATUS_FAIL_TO_POOL,
            ])
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

    /**
     * 根据商户ID获取待审核的审核记录
     * @param $merchantId
     * @param $operId
     * @return MerchantAudit
     */
    public static function getUnauditRecordByMerchantId($merchantId, $operId)
    {
        // 获取最后一条审核记录用到这方法
        $merchantCurrentAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $operId)
            ->whereIn('status', [Merchant::AUDIT_STATUS_AUDITING, Merchant::AUDIT_STATUS_RESUBMIT])
            ->orderByDesc('updated_at')
            ->first();
        return $merchantCurrentAudit;
    }

    /**
     * 审核通过
     * @param $merchant Merchant 要审核的商户
     * @param $auditSuggestion string 审核意见
     * @return Merchant
     */
    public static function auditSuccess($merchant, $auditSuggestion)
    {
        if(is_int($merchant)){
            $merchant = Merchant::findOrFail($merchant);
            if(empty($merchant)){
                throw new ParamInvalidException('商户信息不存在');
            }
        }
        // 获取最后一条审核记录
        $merchantCurrentAudit = self::getUnauditRecordByMerchantId($merchant->id, $merchant->audit_oper_id);

        $merchantCurrentAudit->status = Merchant::AUDIT_STATUS_SUCCESS;
        $merchantCurrentAudit->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchantCurrentAudit->save();

        $merchant->audit_status = Merchant::AUDIT_STATUS_SUCCESS;
        $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';

        // 审核通过时, 补充商户所属运营中心ID及审核通过时间
        $merchant->oper_id = $merchant->audit_oper_id;
        $merchant->active_time = Carbon::now();
        if (!$merchant->first_active_time) {
            $merchant->first_active_time = Carbon::now();
        }
        $merchant->save();

        if ($merchant->oper_biz_member_code) {
            OperBizMember::updateAuditMerchantNumberByCode($merchant->oper_biz_member_code);
        }
        if ($merchant->bizer_id) {
            MyOperBizer::updateAuditMerchantNumberByCode($merchant->oper_id, $merchant->bizer_id);
        }

        return $merchant;
    }

    /**
     * 审核不通过
     * @param $merchant Merchant 要审核的商户
     * @param $auditSuggestion string 审核意见
     * @return Merchant
     */
    public static function auditFail($merchant, $auditSuggestion)
    {
        if(is_int($merchant)){
            $merchant = Merchant::findOrFail($merchant);
            if(empty($merchant)){
                throw new ParamInvalidException('商户信息不存在');
            }
        }
        // 获取最后一条审核记录
        $merchantCurrentAudit = self::getUnauditRecordByMerchantId($merchant->id, $merchant->audit_oper_id);

        $merchantCurrentAudit->status = Merchant::AUDIT_STATUS_FAIL;
        $merchantCurrentAudit->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchantCurrentAudit->save();

        $merchant->audit_status = Merchant::AUDIT_STATUS_FAIL;
        $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';

        $merchant->save();

        return $merchant;
    }

    /**
     * 审核不通过并且打回商户池
     * @param $merchant Merchant 要审核的商户
     * @param $auditSuggestion string 审核意见
     * @return Merchant
     */
    public static function auditFailAndPushToPool($merchant, $auditSuggestion)
    {
        if(is_int($merchant)){
            $merchant = Merchant::findOrFail($merchant);
            if(empty($merchant)){
                throw new ParamInvalidException('商户信息不存在');
            }
        }

        if($merchant->oper_id > 0){
            throw new ParamInvalidException('该商户已有所属运营中心, 不能打回商户池');
        }

        // 获取最后一条审核记录
        $merchantCurrentAudit = self::getUnauditRecordByMerchantId($merchant->id, $merchant->audit_oper_id);
        $merchantCurrentAudit->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchantCurrentAudit->status = Merchant::AUDIT_STATUS_FAIL_TO_POOL;
        $merchantCurrentAudit->save();

        // 更新业务员已发展商户数量
        if ($merchant->bizer_id) {
            MyOperBizer::updateActiveMerchantNumberByCode($merchant->audit_oper_id, $merchant->bizer_id);
        }

        $merchant->audit_status = Merchant::AUDIT_STATUS_FAIL;
        // 打回商户池操作, 需要将商户信息中的audit_oper_id置空
        $merchant->audit_oper_id = 0;
        $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchant->save();

        return $merchant;
    }
}