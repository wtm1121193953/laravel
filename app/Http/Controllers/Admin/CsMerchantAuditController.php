<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Cs\CsMerchantAudit;
use App\Modules\Cs\CsMerchantAuditService;
use App\Modules\Cs\CsMerchantService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CsMerchantAuditController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取审核详情
     */
    public function getAuditDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request()->get('id');
        $userId = request()->get('current_user')->id;

        $merchantAudit = CsMerchantService::getAuditDetail($id,$userId);

        return Result::success($merchantAudit);
    }

    /**
     * 审核操作
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2,3',
            'audit_suggestion' => 'max:50',
        ]);
        $type = request('type');
        $auditSuggestion = request('audit_suggestion', '');
        $merchantAudit = CsMerchantAudit::findOrFail(request()->get('id'));
        $supermarket = null;
        // 如果存在商户ID，则不新增新的商户数据
        if($merchantAudit->cs_merchant_id){
            $supermarket = CsMerchantService::getById($merchantAudit->cs_merchant_id);
            if(!$supermarket){
                // 数据库信息不一致
                throw new ParamInvalidException('错误的操作');
            }
        }

        switch ($type){
            case '1': // 审核通过
                $supermarket = CsMerchantAuditService::auditSuccess($supermarket, $auditSuggestion, $merchantAudit);
                break;
            case '2': // 审核不通过
                $supermarket = CsMerchantAuditService::auditFail($supermarket, $auditSuggestion, $merchantAudit);
                break;
            default:
                throw new BaseResponseException('错误的操作');
        }

        return Result::success($supermarket);
    }

    /**
     * 获取审核记录列表
     */
    public function getAuditList()
    {
        $operId = request('operId');
        $merchantId = request('merchantId');
        $name = request('name');
        $status = request('status');
        $params = [
            'operId' => $operId,
            'merchantId' => $merchantId,
            'name' => $name,
            'status' => $status,
        ];
        $data = CsMerchantAuditService::getAuditResultList($params);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取最新一条审核记录
     */
    public function getNewestAuditRecord()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $merchantId = request('id');
        $record = CsMerchantAuditService::getNewestAuditRecordByMerchantId($merchantId);
        return Result::success($record);
    }

}
