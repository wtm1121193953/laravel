<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class DishesGoodsController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $pageSize = request('pageSize');
        $data = DishesGoods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->with('dishesCategory:id,name')->paginate($pageSize);



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
        $list = DishesGoods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->when($status, function (Builder $query) use ($status){
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
        $dishesGoods = new DishesGoods();
        $dishesGoods->oper_id = request()->get('current_user')->oper_id;
        $dishesGoods->merchant_id = request()->get('current_user')->merchant_id;
        $dishesGoods->name = request('name');
        $dishesGoods->market_price = request('market_price', 0);
        $dishesGoods->sale_price = request('sale_price', 0);
        $dishesGoods->dishes_category_id = request('dishes_category_id',0);
        $dishesGoods->intro = request('intro', '');
        $dishesGoods->logo = request('logo', '');
        $dishesGoods->detail_image = request('detail_image', '');
        $dishesGoods->status = request('status', 1);
        $dishesGoods->is_hot = request('is_hot', 0);

        $dishesGoods->save();

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
        $dishesGoods = DishesGoods::findOrFail(request('id'));
        $dishesGoods->name = request('name');
        $dishesGoods->market_price = request('market_price', 0);
        $dishesGoods->sale_price = request('sale_price', 0);
        $dishesGoods->dishes_category_id = request('dishes_category_id', 0);
        $dishesGoods->intro = request('intro', '');
        $dishesGoods->logo = request('logo', '');
        $dishesGoods->detail_image = request('detail_image', '');
        $dishesGoods->status = request('status', 1);
        $dishesGoods->is_hot = request('is_hot', 0);

        $dishesGoods->save();

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
        $dishesGoods = DishesGoods::findOrFail(request('id'));
        $dishesGoods->status = request('status');

        $dishesGoods->save();
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
        $dishesGoods = DishesGoods::findOrFail(request('id'));
        $dishesGoods->delete();
        return Result::success($dishesGoods);
    }

}