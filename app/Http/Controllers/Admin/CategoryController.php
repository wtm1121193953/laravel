<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Goods\Category;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $data = Category::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getAllList()
    {
        $status = request('status');
        $list = Category::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->get();

        return Result::success([
            'list' => $list,
        ]);
    }

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