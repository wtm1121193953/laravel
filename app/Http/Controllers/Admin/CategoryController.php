<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Goods\Category;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = Category::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'asc')->paginate();

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
        $list = Category::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'asc')->get();

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
        $category = new Category();
        $category->name = request('name');
        $category->status = request('status', 1);

        $category->save();

        return Result::success($category);
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
        $category = Category::findOrFail(request('id'));
        $category->name = request('name');
        $category->status = request('status', 1);

        $category->save();

        return Result::success($category);
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
        $category = Category::findOrFail(request('id'));
        $category->status = request('status');

        $category->save();
        return Result::success($category);
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
        $category = Category::findOrFail(request('id'));
        $category->delete();
        return Result::success($category);
    }

}