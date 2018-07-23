<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantAuditService;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Oper\Oper;
use Illuminate\Support\Collection;
use App\Modules\Oper\OperBizMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;


class MerchantController extends Controller
{

    /**
     * 获取列表 (分页)
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $name = request('name');
        $auditStatus = request('audit_status');
        $status = request('status');
        $signBoardName=request('signBoardName');
        $merchant_category = request('merchant_category');

        if(!empty($merchant_category)){
            if(count($merchant_category)==1){
                $merchant_category_id = intval($merchant_category[0]);
                $merchant_category_final_id = MerchantCategory::where('pid',$merchant_category_id)
                        ->select('id')->get()
                        ->pluck('id');
            }else{
                $merchant_category_final_id = intval($merchant_category[1]);
            }
        }else{
            $merchant_category_final_id= '';
        }
        $data = Merchant::where(function (Builder $query){
                $currentOperId = request()->get('current_user')->oper_id;
                $query->where('oper_id', $currentOperId)
                    ->orWhere('audit_oper_id', $currentOperId);
            })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when($merchant_category_final_id, function (Builder $query) use ($merchant_category_final_id){
                if(is_array($merchant_category_final_id) || $merchant_category_final_id instanceof Collection ){
                    $query->whereIn('merchant_category_id',$merchant_category_final_id);
                }else{
                    $query->where('merchant_category_id', $merchant_category_final_id);
                }
            })
            ->when(!empty($auditStatus), function (Builder $query) use ($auditStatus){
                if($auditStatus == -1){
                    $auditStatus = 0;
                }
                $query->where('audit_status', $auditStatus);
            })
            ->when($name, function (Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->when($signBoardName, function (Builder $query) use ($signBoardName){
                $query->where('signboard_name', 'like', "%$signBoardName%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategoryService::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            $item->account = MerchantAccount::where('merchant_id', $item->id)->first();
            $item->operBizMemberName = OperBizMember::where('oper_id', $item->oper_id)->where('code', $item->oper_biz_member_code)->value('name') ?: '无';
        });

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
        $auditStatus = request('audit_status');
        $status = request('status');
        $list = Merchant::where(function (Builder $query){
            $currentOperId = request()->get('current_user')->oper_id;
            $query->where('oper_id', $currentOperId)
                ->orWhere('audit_oper_id', $currentOperId);
        })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when(!empty($auditStatus), function (Builder $query) use ($auditStatus){
                if($auditStatus == -1){
                    $auditStatus = 0;
                }
                $query->where('audit_status', $auditStatus);
            })
            ->orderBy('updated_at', 'desc')
            ->select('id', 'name')
            ->get();
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
        $id = request('id');
        $merchant = Merchant::findOrFail($id);
        $merchant->categoryPath = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id);
        $merchant->categoryPathOnlyEnable = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id, true);
        $merchant->account = MerchantAccount::where('merchant_id', $merchant->id)->first();

        // 如下是查看 中所需数据
        $merchant->operName = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->value('name');
        $merchant->creatorOperName = Oper::where('id', $merchant->creator_oper_id)->value('name');
        if($merchant->oper_biz_member_code){
            $merchant->operBizMemberName = OperBizMember::where('code', $merchant->oper_biz_member_code)->value('name');
        }
        $oper = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->first();
        if ($oper){
            $merchant->operAddress = $oper->province.$oper->city.$oper->area.$oper->address;
        }
        return Result::success($merchant);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required|max:20',
            'merchant_category_id' => 'required',
            'business_licence_pic_url' => 'required',
            'organization_code' => 'required',
            'settlement_rate' => 'required|numeric|min:0',
            'signboard_name' => 'required|max:20',
        ]);
        $merchant = new Merchant();
        $merchant->fillMerchantPoolInfoFromRequest();
        $merchant->fillMerchantActiveInfoFromRequest();

        // 补充商家创建者及审核提交者
        $currentOperId = request()->get('current_user')->oper_id;
        $merchant->audit_oper_id = $currentOperId;
        $merchant->creator_oper_id = $currentOperId;

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)->first();
        $existsDraft = MerchantDraft::where('name', $merchant->name)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->save();

        // 添加审核记录
        MerchantAuditService::addAudit($merchant->id, $currentOperId);

        // 更新业务员已激活商户数量
        if($merchant->oper_biz_member_code){
            OperBizMember::updateActiveMerchantNumberByCode($merchant->oper_biz_member_code);
        }

        return Result::success($merchant);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'merchant_category_id' => 'required',
            'business_licence_pic_url' => 'required',
            'organization_code' => 'required',
            'settlement_rate' => 'required|numeric|min:0',
        ]);
        $currentOperId = request()->get('current_user')->oper_id;
        $merchant = Merchant::where('id', request('id'))
            ->where('audit_oper_id', $currentOperId)
            ->firstOrFail();

        if(!empty($merchant->oper_biz_member_code)){
            // 记录原业务员ID
            $originOperBizMemberCode = $merchant->oper_biz_member_code;
        }

        $merchant->fillMerchantPoolInfoFromRequest();
        $merchant->fillMerchantActiveInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)
            ->where('id', '<>', $merchant->id)->first();
        $existsDraft = MerchantDraft::where('name', $merchant->name)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }

        if($merchant->oper_id > 0){
            // 如果当前商户已有所属运营中心, 则此次提交为重新提交审核
            // 添加审核记录
            MerchantAuditService::addAudit($merchant->id, $currentOperId,Merchant::AUDIT_STATUS_RESUBMIT);
            $merchant->audit_status = Merchant::AUDIT_STATUS_RESUBMIT;
        }else {
            MerchantAuditService::addAudit($merchant->id, $currentOperId);
            $merchant->audit_status = Merchant::AUDIT_STATUS_AUDITING;
        }

        $merchant->save();

        // 更新业务员已激活商户数量
        if($merchant->oper_biz_member_code){
            OperBizMember::updateActiveMerchantNumberByCode($merchant->oper_biz_member_code);
        }

        // 如果存在原有的业务员, 并且不等于现有的业务员, 更新原有业务员邀请用户数量
        if(isset($originOperBizMemberCode) && $originOperBizMemberCode != $merchant->oper_biz_member_code){
            OperBizMember::updateActiveMerchantNumberByCode($originOperBizMemberCode);
        }

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
        $merchant = Merchant::findOrFail($merchantId);
        if($merchant->oper_id > 0){
            throw new ParamInvalidException('该商户已不在商户池中');
        }
        $currentOperId = request()->get('current_user')->oper_id;
        if($merchant->audit_oper_id > 0){
            throw new ParamInvalidException('该商户已被其他运营中心认领');
        }

        // 补充激活商户需要的信息
        $merchant->fillMerchantActiveInfoFromRequest();
        // 设置当前商户提交审核的运营中心
        $merchant->audit_oper_id = $currentOperId;
        $merchant->audit_status = Merchant::AUDIT_STATUS_AUDITING;

        // 商户名不能重复
        $exists = Merchant::where('name', $merchant->name)
            ->where('id', '<>', $merchant->id)->first();
        if($exists){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchant->save();
        // 添加审核记录
        MerchantAuditService::addAudit($merchant->id, $currentOperId);

        // 更新业务员已激活商户数量
        if($merchant->oper_biz_member_code){
            OperBizMember::updateActiveMerchantNumberByCode($merchant->oper_biz_member_code);
        }

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
        $merchant = Merchant::findOrFail(request('id'));
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
        $merchant = Merchant::findOrFail(request('id'));
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
        $account = MerchantAccount::where('merchant_id', request('merchant_id'))->first();
        if(!empty($account)){
            throw new BaseResponseException('该商户账户已存在, 不能重复创建');
        }
        // 查询账号是否重复
        if(!empty(MerchantAccount::where('account', request('account'))->first())){
            throw new BaseResponseException('帐号重复, 请更换帐号');
        }
        $account = new MerchantAccount();

        $account->oper_id = request()->get('current_user')->oper_id;
        $account->account = request('account');
        $account->merchant_id = request('merchant_id');
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword(request('password'), $salt);
        $account->save();

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
        $account = MerchantAccount::findOrFail(request('id'));
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword(request('password'), $salt);

        $account->save();

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