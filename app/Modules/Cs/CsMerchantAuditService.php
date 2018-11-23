<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Oper\MyOperBizer;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizMember;
use App\ResultCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->when(isset($params['status']), function (Builder $query) use ($params){
                $query->where('status',$params['status']);
            })
            ->orderByDesc('updated_at')->paginate();
        $data->each(function($item) {
            $item->operName = Oper::where('id', $item->oper_id)->value('name');
            $item->data_after = json_decode($item->data_after,true);
            if($item->cs_merchant_id){
                $item->cs_merchant_detail = CsMerchantService::getById($item->cs_merchant_id);
            }
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
     * @return CsMerchant
     */
    public static function getNewestAuditRecordByMerchantId($merchantId)
    {
        $merchant = CsMerchant::where('id', $merchantId)
            ->select('id', 'name','audit_suggestion')
            ->first();

        return $merchant;
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
        $audit->settlement_cycle_type = CsMerchant::SETTLE_DAY_ADD_ONE;
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
     * @param $merchant CsMerchant|null 要审核的商户
     * @param $auditSuggestion string 审核意见
     * @param $merchantAudit CsMerchantAudit 审核记录
     * @return CsMerchant|null
     * @throws \Exception
     */
    public static function auditSuccess($merchant, $auditSuggestion, $merchantAudit)
    {
        if(is_null($merchant)){
            // 商户表无数据则新增
            $merchant = new CsMerchant();
            $saveColumn = json_decode($merchantAudit->data_after);
            foreach ($saveColumn as $k=>$v){
                $merchant->$k = $v;
            }
        }
        $merchant->audit_status = CsMerchant::AUDIT_STATUS_SUCCESS;
        $merchant->status = CsMerchant::AUDIT_STATUS_SUCCESS;
        $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchant->oper_id = $merchant->audit_oper_id;
        $merchant->active_time = Carbon::now();
        if (!$merchant->first_active_time) {
            $merchant->first_active_time = Carbon::now();
        }

        // 修改审核记录状态
        $merchantAudit->suggestion = $auditSuggestion ? $auditSuggestion:'';
        $merchantAudit->status = CsMerchantAudit::AUDIT_STATUS_SUCCESS;

        // 开启事务
        DB::beginTransaction();
        try{
            $merchant->save();
            $merchantAudit->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
        }

        return $merchant;

    }

    /**
     * 审核不通过
     * @param $merchant |null CsMerchant 要审核的商户
     * @param $auditSuggestion string 审核意见
     * @param $merchantAudit CsMerchantAudit
     * @return Merchant
     * @throws \Exception
     */
    public static function auditFail($merchant, $auditSuggestion, $merchantAudit)
    {
        if(!is_null($merchant)){
            $merchant->audit_status = CsMerchant::AUDIT_STATUS_FAIL;
            $merchant->audit_suggestion = $auditSuggestion ? $auditSuggestion:'';
        }
        $merchantAudit->status = Merchant::AUDIT_STATUS_FAIL;
        $merchantAudit->suggestion = $auditSuggestion ? $auditSuggestion:'';

        // 开启事务
        DB::beginTransaction();
        try{
            if(!is_null($merchant)){
                $merchant->save();
            }
            $merchantAudit->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
        }
        return $merchant;
    }

}