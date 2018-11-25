<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Exports\AdminCsMerchantExport;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminUser;
use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantAudit;
use App\Modules\Cs\CsMerchantAuditService;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantAccountService;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperService;
use App\Result;
use http\Exception\BadMessageException;


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
        $creatorOperId = request('creatorOperId');
        // 根据输入的运营中心名称获取录入信息的运营中心ID列表
        $creatorOperName = request('creatorOperName');
        if($creatorOperName){
            $createOperIds = OperService::getAll(['name' => $creatorOperName], 'id')->pluck('id');
        }



        $data = CsMerchantService::getList([
            'id' => $id,
            'operId' => $operIds ?? $operId,
            'name' => $name,
            'signboardName' => $signboardName,
            'creatorOperId' => $createOperIds ?? $creatorOperId,
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
     * 获取全部的商户名称
     */
    public function allNames()
    {
        $data = [
            'audit_status' => request('audit_status'),
            'status' => request('status'),
            'isPilot' => request('isPilot'),
            'operId' => request()->get('current_user')->oper_id,
        ];
        $list = CsMerchantService::getAllNames($data);
        foreach ($list as $key){
            $key->name = $key->id.":".$key->name;
        }
        return Result::success([
            'list' => $list
        ]);
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

        /*$isPayToPlatform = $this->isPayToPlatform();
        if($isPayToPlatform){
            $merchant->settlement_cycle_type = 7;//运营中心切换到平台，显示为未知
        }else{
            $merchant->settlement_cycle_type = 1;//运营中心切未换到平台，显示为周结
        }*/

        return Result::success($merchant);
    }

    /**
     * 编辑
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
        $merchant = CsMerchantService::edit(request('id'), request('audit_oper_id'),request('audit_status'),true);

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

    /**
     * 删除
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $merchant = CsMerchantService::getById(request('id'));
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchant->delete();
        return Result::success($merchant);
    }

    /**
     * 创建商户账号
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createAccount()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
            'account' => 'required',
            'password' => 'required|min:6',
        ]);
        $merchantId = request('merchant_id');
        $account = request('account');
        $operId = request()->get('current_user')->oper_id;
        $password = request('password');

        $account = MerchantAccountService::createAccount($merchantId,$account,$operId,$password);

        return Result::success($account);
    }

    /**
     * 编辑商户账号信息, 即修改密码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function editAccount()
    {

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
        ]);
        $id = request('id');
        $password = request('password');

        $account = MerchantAccountService::editAccount($id,$password);

        return Result::success($account);
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

    //判断运营中心是否切换到平台
    public function isPayToPlatform(){

        $oper = OperService::getById(request()->get('current_user')->oper_id);

        $isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);

        return $isPayToPlatform;
    }

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
       /* var_dump($type);
    exix();*/
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


}