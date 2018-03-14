<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Item\Item;
use App\Result;

class ItemController extends Controller
{

    public function getList()
    {
        $data = Item::orderBy('id', 'desc')->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $item = new Item();
        $item->name = request('name');
        $item->status = request('status', 1);

        $item->save();

        return Result::success($item);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $item = Item::findOrFail(request('id'));
        $item->name = request('name');
        $item->status = request('status', 1);

        $item->save();

        return Result::success($item);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $item = Item::findOrFail(request('id'));
        $item->status = request('status');

        $item->save();
        return Result::success($item);
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
        $item = Item::findOrFail(request('id'));
        $item->delete();
        return Result::success($item);
    }

}