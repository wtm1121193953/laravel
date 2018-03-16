<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Item\Item;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class ItemController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $data = Item::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'supplier_id' => 'required|integer',
            'category_id' => 'required|integer',
            'origin_price' => 'required|numeric|min:0',
        ]);
        $item = new Item();
        $item->name = request('name');
        $item->supplier_id = request('supplier_id');
        $item->category_id = request('category_id');
        $item->pict_url = request('pict_url', '');
        $item->detail = request('detail', '');
        $item->small_images = request('small_images', '');
        $item->total_count = request('total_count', 0);
        $item->origin_price = request('origin_price');
        $item->discount_price = request('discount_price', request('origin_price'));

        $item->status = request('status', 1);

        $item->save();

        return Result::success($item);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'supplier_id' => 'required|integer',
            'category_id' => 'required|integer',
            'origin_price' => 'required|numeric|min:0',
        ]);
        $item = Item::findOrFail(request('id'));
        $item->name = request('name');
        $item->supplier_id = request('supplier_id');
        $item->category_id = request('category_id');
        $item->pict_url = request('pict_url', '');
        $item->detail = request('detail', '');
        $item->small_images = request('small_images', '');
        $item->origin_price = request('origin_price');
        $item->discount_price = request('discount_price', request('origin_price'));

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

    public function changeLeftCount()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'leftCount' => 'required|integer|min:0'
        ]);
        $leftCount = request('leftCount', 0);
        $item = Item::findOrFail(request('id'));
        $item->total_count = $item->sell_count + $leftCount;
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