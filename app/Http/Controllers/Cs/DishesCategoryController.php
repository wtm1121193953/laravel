<?php

namespace App\Http\Controllers\Cs;


use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesCategoryService;
use App\Result;

class DishesCategoryController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $pageSize = request('pageSize');

        $data = DishesCategoryService::getListByMerchantId(
            request()->get('current_user')->merchant_id, $status, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取全部列表
     */
    public function getAllList()
    {
        $status = request('status');

        $list = DishesCategoryService::getAllList(
            request()->get('current_user')->merchant_id, $status);

        return Result::success([
            'list' => $list,
        ]);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);

        $dishesCategory = DishesCategoryService::add(
            request()->get('current_user')->oper_id,
            request()->get('current_user')->merchant_id,
            request('name'),
            request('status', 1)
        );

        return Result::success($dishesCategory);
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
        $dishesCategory = DishesCategoryService::edit(
            request('id'),
            request()->get('current_user')->merchant_id,
            request('name'),
            request('status', 1)
        );

        return Result::success($dishesCategory);
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
        $dishesCategory = DishesCategoryService::changeStatus(
            request('id'),
            request()->get('current_user')->merchant_id,
            request('status')
        );
        return Result::success($dishesCategory);
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
        $categoryId = request('id');
        $dishesCategory = DishesCategoryService::del($categoryId, request()->get('current_user')->merchant_id);
        return Result::success($dishesCategory);
    }

    /**
     * 上下移动
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function saveOrder()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'type' => 'required',
        ]);
        $type = request('type');
        $merchantId = request()->get('current_user')->merchant_id;

        DishesCategoryService::changeSort(request('id'), $merchantId, $type);

        return Result::success();
    }

}