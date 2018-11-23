<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\MyOperBizer;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CsMerchantAuditService extends BaseService {

    /**
     * 获取审核结果列表
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getAuditResultList(array $params = [])
    {

        $data = CsMerchantAudit::when(isset($params['oper_id']), function (Builder $query) use ($params){
            $query->where('oper_id', $params['oper_id']);
        })
            ->orderByDesc('updated_at')->paginate();

        $data->each(function($item) {
            $item->operName = Oper::where('id', $item->oper_id)->value('name');
            /*// 解析JSON格式
            $detail = json_decode($item->data_after,true);
            if($item['cs_merchant_id']!=0){
                $item->csMerchant = CsMerchant::find($item->cs_merchant_id);
            }
            foreach ($detail as $k => $v){
                if(!$v){
                    continue;
                }
                $item->$k = $v;
            }*/
        });
        return $data;
    }

    /**
     * 根据ID获取商户信息
     * @param $id
     * @param array|string $fields
     * @return CsMerchantAudit
     */
    public static function getById($id, $fields = ['*'])
    {
        if(is_string($fields)){
            $fields = explode(',', $fields);
        }
        return CsMerchantAudit::find($id, $fields);
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

    public static function addAudit($params)
    {
        $operId = array_get($params,'oper_id',0);
        $type = array_get($params,'type',CsMerchantAudit::INSERT_TYPE);
        $CsmerchantId = array_get($params,'CsmerchantId',0);
        $name = array_get($params,'name','');
        $dataBefore = array_get($params,'dataBefore','');
        $dataAfter = array_get($params,'dataAfter','');
        $dataModify = array_get($params,'dataModify','');

        $audit = new CsMerchantAudit();
        $audit->oper_id = $operId;
        $audit->type = $type;
        $audit->cs_merchant_id = $CsmerchantId;
        $audit->name = $name;
        $audit->data_before = $dataBefore;
        $audit->data_after = $dataAfter;
        $audit->data_modify = $dataModify;
        $audit->status = CsMerchantAudit::AUDIT_STATUS_AUDITING;
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