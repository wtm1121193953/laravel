<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Oper\OperBizMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MerchantDraftController extends Controller
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
        $data = MerchantDraft::where(function (Builder $query){
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
            ->when($name, function (Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->orderBy('updated_at', 'desc')
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

    public function detail()
    {
        $id = request('id');
        $merchantDraft = MerchantDraft::findOrFail($id);
        $merchantDraft->categoryPath = $merchantDraft->merchant_category_id ? MerchantCategoryService::getCategoryPath($merchantDraft->merchant_category_id) : [];
        $merchantDraft->account = MerchantAccount::where('merchant_id', $merchantDraft->id)->first();
        return Result::success($merchantDraft);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $merchantDraft = new MerchantDraft();
        $merchantDraft->fillMerchantPoolInfoFromRequest();
        $merchantDraft->fillMerchantActiveInfoFromRequest();

        // 补充商家创建者及审核提交者
        $currentOperId = request()->get('current_user')->oper_id;
        $merchantDraft->audit_oper_id = $currentOperId;
        $merchantDraft->creator_oper_id = $currentOperId;

        // 商户名不能重复
        $exists = Merchant::where('name', $merchantDraft->name)->first();
        $existsDraft = MerchantDraft::where('name', $merchantDraft->name)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchantDraft->save();
        $count = MerchantDraft::where('creator_oper_id', $currentOperId)->count();
        return Result::success([
            'data' => $merchantDraft,
            'count' => $count,
        ]);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $currentOperId = request()->get('current_user')->oper_id;
        $merchantDraft = MerchantDraft::where('id', request('id'))
            ->where('audit_oper_id', $currentOperId)
            ->firstOrFail();

        $merchantDraft->fillMerchantPoolInfoFromRequest();
        $merchantDraft->fillMerchantActiveInfoFromRequest();

        // 商户名不能重复
        $exists = Merchant::where('name', $merchantDraft->name)->first();
        $existsDraft = MerchantDraft::where('name', $merchantDraft->name)
            ->where('id', '<>', $merchantDraft->id)->first();
        if($exists || $existsDraft){
            throw new ParamInvalidException('商户名称不能重复');
        }

        $merchantDraft->save();

        return Result::success($merchantDraft);
    }

    /**
     * 删除草稿
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete()
    {
        $this->validate(request(), [
           'id' => 'required|integer|min:1'
        ]);
        $result = MerchantDraft::destroy(request('id'));

        $currentOperId = request()->get('current_user')->oper_id;
        $count = MerchantDraft::where('creator_oper_id', $currentOperId)->count();
        return Result::success([
            'result' => $result,
            'count' => $count,
        ]);
    }
}