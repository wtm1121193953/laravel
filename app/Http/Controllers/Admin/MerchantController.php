<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 15:34
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Exports\MerchantExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Result;
use Illuminate\Support\Facades\Log;

class MerchantController extends Controller
{

    /**
     * 获取商户列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {

        $id = request('merchantId');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $name = request('name');
        $signboardName = request('signboardName');
        $status = request('status');
        $auditStatus = request('auditStatus');
        $merchantCategory = request('merchantCategory');
        $isPilot = request('isPilot');

        if(is_string($auditStatus)){
            $auditStatus = explode(',', $auditStatus);
        }

        $operId = request('operId');

        // 根据输入的运营中心名称获取所属运营中心ID列表
        $operName = request('operName');
        if($operName) {
            $operIds = OperService::getAll(['name' => $operName], 'id')->pluck('id');
        }
        $creatorOperId = request('creatorOperId');
        // 根据输入的运营中心名称获取录入信息的运营中心ID列表
        $creatorOperName = request('creatorOperName');
        if($creatorOperName){
            $createOperIds = OperService::getAll(['name' => $creatorOperName], 'id')->pluck('id');
        }
        $startTime = microtime(true);
        $data = MerchantService::getList([
            'id' => $id,
            'operId' => $operIds ?? $operId,
            'name' => $name,
            'signboardName' => $signboardName,
            'creatorOperId' => $createOperIds ?? $creatorOperId,
            'status' => $status,
            'auditStatus' => $auditStatus,
            'merchantCategory' => $merchantCategory,
            'isPilot' => $isPilot,
            'startCreatedAt' => $startDate,
            'endCreatedAt' => $endDate,
        ]);
        $endTime = microtime(true);

        Log::info('耗时: ', ['start time' => $startTime, 'end time' => $endTime, '耗时: ' => $endTime - $startTime]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $merchant = MerchantService::detail(request('id'));
        //增加最后审核时间
        return Result::success($merchant);
    }

    /**
     * 获取审核记录列表
     */
    public function getAuditList()
    {
        $data = MerchantAuditService::getAuditResultList();
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
        $record = MerchantAuditService::getNewestAuditRecordByMerchantId($merchantId);
        return Result::success($record);
    }

    /**
     * 审核商户
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required|integer|in:1,2,3',
            'audit_suggestion' => 'max:50',
        ]);

        $type = request('type');
        $merchantId = request('id');
        $auditSuggestion = request('audit_suggestion', '');

        $merchant = MerchantService::getById($merchantId);
        if(empty($merchant)){
            throw new ParamInvalidException('商户信息不存在');
        }

        // 兼容旧操作, 没有审核记录时创建一条审核记录, 以便于继续走下去
        $merchantCurrentAudit = MerchantAudit::where('merchant_id', $merchantId)
            ->where('oper_id', $merchant->audit_oper_id)
            ->whereIn('status', [0,3])
            ->orderBy('updated_at','desc')
            ->first();
        if(empty($merchantCurrentAudit)){
            MerchantAuditService::addAudit($merchantId, $merchant->audit_oper_id);
        }

        switch ($type){
            case '1': // 审核通过
                $merchant = MerchantAuditService::auditSuccess($merchant, $auditSuggestion);
                break;
            case '2': // 审核不通过
                $merchant = MerchantAuditService::auditFail($merchant, $auditSuggestion);
                break;
            case '3': // 审核不通过并打回到商户池
                $merchant = MerchantAuditService::auditFailAndPushToPool($merchant, $auditSuggestion);
                break;
            default:
                throw new BaseResponseException('错误的操作');
        }

        return Result::success($merchant);
    }

    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel()
    {
        $id = request('merchantId');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $name = request('name');
        $status = request('status');
        $auditStatus = request('auditStatus');
        $signboardName = request('signboardName');
        if ($auditStatus || $auditStatus==="0"){
            $auditStatus = explode(',', $auditStatus);
        }
        $operId = request('operId');
        $operName = request('operName');
//        $creatorOperId = request('creatorOperId');
//        $creatorOperName = request('creatorOperName');

        $merchantCategory = request('merchantCategory', '');
        $isPilot = request('isPilot', 0);

        return (new MerchantExport($id, $startDate, $endDate,$signboardName, $name, $status, $auditStatus, $operId, $operName, $merchantCategory, $isPilot))->download('商户列表.xlsx');
    }

    /**
     * 修改商户状态
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $merchant = MerchantService::getById(request('id'));
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchant->status = $merchant->status == Merchant::STATUS_ON ? Merchant::STATUS_OFF : Merchant::STATUS_ON;
        $merchant->save();

        return Result::success($merchant);
    }

    /**
     * SaaS端 编辑试点商户
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $validate = [
            'name' => 'required|max:20',
            'merchant_category_id' => 'required',
            'signboard_name' => 'required|max:20',
        ];
        if (request('is_pilot') !== Merchant::PILOT_MERCHANT){
            $validate = array_merge($validate, [
                'business_licence_pic_url' => 'required',
                'organization_code' => 'required',
                'settlement_rate' => 'required|numeric|min:0',
            ]);
        }
        $this->validate(request(), $validate);

        $mobile = request('contacter_phone');
        if(!preg_match('/^1[3,4,5,6,7,8,9]\d{9}$/', $mobile)){
            throw new ParamInvalidException('负责人手机号码不合法');
        }

        $merchant = MerchantService::edit(request('id'), request('audit_oper_id'));

        return Result::success($merchant);
    }
}