<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Merchant\MerchantDraftService;
use App\Result;

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
        $currentOperId = request()->get('current_user')->oper_id;

        $data = MerchantDraftService::getList($currentOperId,$status,$auditStatus,$name);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $id = request('id');
        $merchantDraft = MerchantDraftService::detail($id);

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

        $currentOperId = request()->get('current_user')->oper_id;

        $merchantDraft = MerchantDraftService::add($currentOperId);

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

        $id = request('id');

        $currentOperId = request()->get('current_user')->oper_id;

        $merchantDraft = MerchantDraftService::edit($id,$currentOperId);

        return Result::success($merchantDraft);
    }

    /**
     * 删除草稿
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function delete()
    {
        $this->validate(request(), [
           'id' => 'required|integer|min:1'
        ]);

        $merchantDraft = MerchantDraftService::getById(request('id'));
        if(empty($merchantDraft)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $merchantDraft->delete();

        $currentOperId = request()->get('current_user')->oper_id;
        $count = MerchantDraft::where('creator_oper_id', $currentOperId)->count();
        return Result::success([
            'result' => $merchantDraft,
            'count' => $count,
        ]);
    }
}