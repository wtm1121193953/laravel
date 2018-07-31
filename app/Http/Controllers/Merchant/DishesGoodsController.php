<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoodsService;
use App\Result;

class DishesGoodsController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $pageSize = request('pageSize');
        $name = request('name', '');
        $categoryId = request('category_id', '');

        $data = DishesGoodsService::getList([
            'merchantId' => request()->get('current_user')->merchant_id,
            'name' => $name,
            'status' => $status,
            'categoryId' => $categoryId,
        ], $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'showSort'=>$categoryId ? 1:0,
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
        $merchantId = request()->get('current_user')->merchant_id;
        $operId = request()->get('current_user')->oper_id;

        $dishesGoods = DishesGoodsService::addFromRequest($operId, $merchantId);

        return Result::success($dishesGoods);
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
        $merchantId = request()->get('current_user')->merchant_id;
        $dishesGoods = DishesGoodsService::editFromRequest(request('id'), $merchantId);

        return Result::success($dishesGoods);
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

        $merchantId = request()->get('current_user')->merchant_id;
        $dishesGoods = DishesGoodsService::changeStatus(request('id'), $merchantId, request('status'));

        return Result::success($dishesGoods);
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
        $merchantId = request()->get('current_user')->merchant_id;
        $dishesGoods = DishesGoodsService::del(request('id'), $merchantId);
        return Result::success($dishesGoods);
    }

    /**
     * 单品排序
     */
    public function saveOrder(){
        $type = request('type');
        $categoryId = request('category_id', '');
        $merchantId = request()->get('current_user')->merchant_id;
        DishesGoodsService::changeSort(request('id'), $merchantId, $categoryId, $type);
        return Result::success();
    }

}