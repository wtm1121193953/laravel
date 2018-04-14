<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class GoodsController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = Goods::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

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
        $list = Goods::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->get();

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
        $goods = new Goods();
        $goods->name = request('name');
        $goods->status = request('status', 1);

        $goods->save();

        return Result::success($goods);
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
        $goods = Goods::findOrFail(request('id'));
        $goods->name = request('name');
        $goods->status = request('status', 1);

        $goods->save();

        return Result::success($goods);
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
        $goods = Goods::findOrFail(request('id'));
        $goods->status = request('status');

        $goods->save();
        return Result::success($goods);
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
        $goods = Goods::findOrFail(request('id'));
        $goods->delete();
        return Result::success($goods);
    }

}