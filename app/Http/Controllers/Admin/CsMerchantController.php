<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Exports\AdminCsMerchantExport;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Oper\OperService;
use App\Result;


class CsMerchantController extends Controller
{

    /**
     * 获取列表 (分页)
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
        $settlementCycleType = request('settlementCycleType');
        $auditStatus = request('auditStatus');
        $merchantCategory = request('merchantCategory');

        if(is_string($auditStatus)){
            $auditStatus = explode(',', $auditStatus);
        }

        if(is_string($settlementCycleType)){
            $settlementCycleType = explode(',', $settlementCycleType);
        }

        $operId = request('operId');

        // 根据输入的运营中心名称获取所属运营中心ID列表
        $operName = request('operName');
        if($operName) {
            $operIds = OperService::getAll(['name' => $operName], 'id')->pluck('id');
        }

        $data = CsMerchantService::getList([
            'id' => $id,
            'operId' => $operIds ?? $operId,
            'name' => $name,
            'signboardName' => $signboardName,
            'status' => $status,
            'settlementCycleType' => $settlementCycleType,
            'auditStatus' => $auditStatus,
            'merchantCategory' => $merchantCategory,
            'startCreatedAt' => $startDate,
            'endCreatedAt' => $endDate,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 商户编辑（直接编辑）
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit()
    {
        $validate = [
            'name' => 'required|max:50',
            'signboard_name' => 'required|max:20',
        ];
        if (request('is_pilot') !== CsMerchant::PILOT_MERCHANT){
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
        $merchant = CsMerchantService::edit(request('id'), request()->get('oper_id'),request('audit_status'),true);

        return Result::success($merchant);
    }

    /**
     * 导出商户
     */
    public function export(){

        $id = request('merchantId');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $name = request('name');
        $status = request('status');
        $settlementCycleType = request('settlementCycleType');
        $auditStatus = request('auditStatus');
        $signboardName = request('signboardName');
        if ($auditStatus || $auditStatus==="0"){
            $auditStatus = explode(',', $auditStatus);
        }
        if(is_string($settlementCycleType)){
            $settlementCycleType = explode(',', $settlementCycleType);
        }
        $operId = request('operId');
        $operName = request('operName');

        $merchantCategory = request('merchantCategory', '');

        return (new AdminCsMerchantExport($id, $startDate, $endDate,$signboardName, $name, $status, $settlementCycleType, $auditStatus, $operId, $operName, $merchantCategory))->download('超市商户列表.xlsx');

    }

    /**
     * 详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $userId = request()->get('current_user')->id;
        $merchant = CsMerchantService::detail(request('id'),$userId);
        return Result::success($merchant);
    }

    /**
     * 修改状态
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $merchant = CsMerchantService::getById(request('id'));
        if(empty($merchant)){
            throw new DataNotFoundException('超市商户信息不存在');
        }
        $merchant->status = request('status');
        $merchant->save();
        return Result::success($merchant);
    }

}