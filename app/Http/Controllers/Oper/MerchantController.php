<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantCategory;
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
        $status = request('status');
        $data = Merchant::where(function (Builder $query){
                $currentOperId = request()->get('current_user')->oper_id;
                $query->where('oper_id', $currentOperId)
                    ->orWhere('audit_oper_id', $currentOperId);
            })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate();

        $data->each(function ($item){
            if ($item->merchant_category_id){
                $item->categoryPath = MerchantCategory::getCategoryPath($item->merchant_category_id);
            }
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            $item->account = MerchantAccount::where('merchant_id', $item->id)->first();
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $id = request('id');
        $merchant = Merchant::findOrFail($id);
        $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
        $merchant->account = MerchantAccount::where('merchant_id', $merchant->id)->first();
        return Result::success($merchant);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'merchant_category_id' => 'required',
            'business_licence_pic_url' => 'required',
        ]);
        $merchant = new Merchant();
        $merchant->fillMerchantPoolInfoFromRequest();
        $merchant->fillMerchantActiveInfoFromRequest();

        // 补充商家创建者及审核提交者
        $currentOperId = request()->get('current_user')->oper_id;
        $merchant->audit_oper_id = $currentOperId;
        $merchant->creator_oper_id = $currentOperId;

        // 商户营业执照代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户营业执照代码已存在');
        }
        $merchant->save();

        // 添加审核记录
        MerchantAudit::addRecord($merchant->id, $currentOperId);

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
        ]);
        $currentOperId = request()->get('current_user')->oper_id;
        $merchant = Merchant::where('id', request('id'))
            ->where('audit_oper_id', $currentOperId)
            ->firstOrFail();

        $merchant->fillMerchantPoolInfoFromRequest();
        $merchant->fillMerchantActiveInfoFromRequest();

        // 商户营业执照代码不能重复
        $existMerchant = Merchant::where('organization_code', $merchant->organization_code)->offset(1)->first();
        if(!empty($existMerchant)) {
            throw new BaseResponseException('商户营业执照代码已存在');
        }

        if($merchant->audit_status != Merchant::AUDIT_STATUS_AUDITING){
            // 如果当前不是审核中, 则此次提交为重新提交审核
            MerchantAudit::resubmit($merchant->id, $currentOperId);
            $merchant->audit_status = Merchant::AUDIT_STATUS_RESUBMIT;
        }else {
            $merchant->audit_status = Merchant::AUDIT_STATUS_AUDITING;
        }

        $merchant->save();

        return Result::success($merchant);
    }

    /**
     * 从商户池添加商户, 即补充商户池中商户的合同信息
     */
    public function addFromMerchantPool()
    {
//        throw new BaseResponseException('等待完成');
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

        $merchant->save();
        // 添加审核记录
        MerchantAudit::addRecord($merchant->id, $currentOperId);

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

        $merchant->categoryPath = MerchantCategory::getCategoryPath($merchant->merchant_category_id);
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
        $account = MerchantAccount::where('merchant_id', request('id'))->first();
        if(!empty($account)){
            throw new BaseResponseException('该商户账户已存在, 不能重复创建');
        }
        // 查询账号是否重复
        if(!empty(MerchantAccount::where('account', request('account'))->first())){
            throw new BaseResponseException('账号重复, 请更换账号');
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
        $data = MerchantAudit::where('oper_id', request()->get('current_user')->oper_id)
            ->whereIn('status', [
                Merchant::AUDIT_STATUS_SUCCESS,
                Merchant::AUDIT_STATUS_FAIL,
                Merchant::AUDIT_STATUS_FAIL_TO_POOL,
            ])
            ->orderByDesc('updated_at')
            ->paginate();
        $data->each(function($item) {
            $item->merchantName = Merchant::where('id', $item->merchant_id)->value('name');
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}