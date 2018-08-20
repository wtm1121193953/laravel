<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantAccountService;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Merchant\MerchantService;
use App\Result;


class MerchantController extends Controller
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
            'signboardName' => request('signboardName'),
            'status' => request('status'),
            'auditStatus' => request('audit_status'),
            'merchantCategory' => request('merchant_category'),
            'isPilot' => request('isPilot'),
            'startCreatedAt' => request('startCreatedAt'),
            'endCreatedAt' => request('endCreatedAt'),
        ];

        $data = MerchantService::getList($data);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取全部的商户名称
     */
    public function allNames()
    {
        $data = [
            'audit_status' => request('audit_status'),
            'status' => request('status'),
            'operId' => request()->get('current_user')->oper_id,
        ];
        $list = MerchantService::getAllNames($data);
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
        $merchant = MerchantService::detail(request('id'));
        return Result::success($merchant);
    }

    /**
     * 添加数据
     */
    public function add()
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

        $mobile = request('contacter_phone');
        if(!preg_match('/^1[3,4,5,6,7,8,9]\d{9}$/', $mobile)){
            throw new ParamInvalidException('负责人手机号码不合法');
        }
        $this->validate(request(), $validate);

        $currentOperId = request()->get('current_user')->oper_id;

        $merchant = MerchantService::add($currentOperId);

        return Result::success($merchant);
    }

    /**
     * 编辑
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

        $merchant = MerchantService::edit(request('id'),request()->get('current_user')->oper_id);

        return Result::success($merchant);
    }

    /**
     * 从商户池添加商户, 即补充商户池中商户的合同信息
     */
    public function addFromMerchantPool()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'business_licence_pic_url' => 'required',
            'organization_code' => 'required',
            'settlement_rate' => 'required|numeric|min:0',
        ]);

        $merchantId = request('id');
        $merchant = MerchantService::getById($merchantId);
        if(empty($merchant)){
            throw new DataNotFoundException('商户池信息不存在');
        }
        if($merchant->oper_id > 0){
            throw new ParamInvalidException('该商户已不在商户池中');
        }
        if($merchant->audit_oper_id > 0){
            throw new ParamInvalidException('该商户已被其他运营中心认领');
        }

        $currentOperId = request()->get('current_user')->oper_id;
        $merchant = MerchantService::addFromMerchantPool($currentOperId, $merchant);

        return Result::success('操作成功', $merchant);
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
        $merchant = MerchantService::getById(request('id'));
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchant->status = request('status');
        $merchant->save();

        $merchant->categoryPath = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id);
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
        $merchant = MerchantService::getById(request('id'));
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


}