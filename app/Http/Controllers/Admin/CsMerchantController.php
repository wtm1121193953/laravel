<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Exports\AdminCsMerchantExport;
use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantAccountService;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizerService;
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
        $data = [
            'id' => request('id'),
            'operId' => request()->get('current_user')->oper_id,
            'creatorOperId' => request('creatorOperId'),
            'name' => request('name'),
            'merchantId' => request('merchantId'),
            'signboardName' => request('signboardName'),
            'status' => request('status'),
            'auditStatus' => request('audit_status'),
            //'merchantCategory' => request('merchant_category'),
            'isPilot' => request('isPilot'),
            'startCreatedAt' => request('startCreatedAt'),
            'endCreatedAt' => request('endCreatedAt'),
        ];

        $data = CsMerchantService::getList($data);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出商户
     */
    public function export(){

        $data = [
            'id' => request('id'),
            'operId' => request()->get('current_user')->oper_id,
            'creatorOperId' => request('creatorOperId'),
            'name' => request('name'),
            'merchantId' => request('merchantId'),
            'signboardName' => request('signboardName'),
            'status' => request('status'),
            'auditStatus' => request('audit_status'),
            'startCreatedAt' => request('startCreatedAt'),
            'endCreatedAt' => request('endCreatedAt'),
        ];

        $query = CsMerchantService::getList($data,true);

        $list = $query->get();

        return (new AdminCsMerchantExport($list,request('isPilot')))->download('超市商户列表.xlsx');

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
        $merchant = CsMerchantService::detail(request('id'));

        /*$isPayToPlatform = $this->isPayToPlatform();
        if($isPayToPlatform){
            $merchant->settlement_cycle_type = 7;//运营中心切换到平台，显示为未知
        }else{
            $merchant->settlement_cycle_type = 1;//运营中心切未换到平台，显示为周结
        }*/

        return Result::success($merchant);
    }

    /**
     * 添加数据
     */
    public function add()
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

        $mobile = request('contacter_phone');
        if(!preg_match('/^1[3456789]\d{9}$/', $mobile)){
            throw new ParamInvalidException('负责人手机号码不合法');
        }
        $this->validate(request(), $validate);

        $currentOperId = request()->get('current_user')->oper_id;

        $merchant = CsMerchantService::add($currentOperId);

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
        $merchant = CsMerchantService::edit(request('id'), request('audit_oper_id'),request('audit_status'));

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
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchant->status = request('status');
        $merchant->save();

        //$merchant->categoryPath = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id);
        $merchant->account = MerchantAccount::where('merchant_id', $merchant->id)->first();

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

    public function getAuditList()
    {
        $data = MerchantAuditService::getAuditResultList(['oper_id' => request()->get('current_user')->oper_id]);
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

    //判断运营中心是否切换到平台
    public function isPayToPlatform(){

        $oper = OperService::getById(request()->get('current_user')->oper_id);

        $isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);

        return $isPayToPlatform;
    }


}