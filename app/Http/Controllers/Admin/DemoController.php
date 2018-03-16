<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Demo\Demo;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class DemoController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = Demo::when($status, function (Builder $query) use ($status){
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
        $list = Demo::when($status, function (Builder $query) use ($status){
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
        $demo = new Demo();
        $demo->name = request('name');
        $demo->status = request('status', 1);

        $demo->save();

        return Result::success($demo);
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
        $demo = Demo::findOrFail(request('id'));
        $demo->name = request('name');
        $demo->status = request('status', 1);

        $demo->save();

        return Result::success($demo);
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
        $demo = Demo::findOrFail(request('id'));
        $demo->status = request('status');

        $demo->save();
        return Result::success($demo);
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
        $demo = Demo::findOrFail(request('id'));
        $demo->delete();
        return Result::success($demo);
    }

}