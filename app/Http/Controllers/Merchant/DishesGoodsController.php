<?php

namespace App\Http\Controllers\Merchant;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        $data = DishesGoods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when($name, function (Builder $query) use ($name) {
                $query->where('name', 'like', "%$name%");
            })
            ->when($categoryId, function (Builder $query) use ($categoryId) {
                $query->where('dishes_category_id', $categoryId);
            })
            ->orderBy('sort', 'desc')
            ->with('dishesCategory:id,name')
            ->paginate($pageSize);



        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'showSort'=>$categoryId ? 1:0,
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
        $merchantId = request()->get('current_user')->merchant_id;
        $dishesGoodsList = DishesGoods::where('merchant_id', $merchantId)
            ->where('name', request('name'))
            ->get();
        if (count($dishesGoodsList) > 0){
            throw new BaseResponseException('商品名称重复！');
        }

        $dishesGoods = new DishesGoods();
        $dishesGoods->oper_id = request()->get('current_user')->oper_id;
        $dishesGoods->merchant_id = $merchantId;
        $dishesGoods->name = request('name');
        $dishesGoods->market_price = request('market_price', 0);
        $dishesGoods->sale_price = request('sale_price', 0);
        $dishesGoods->dishes_category_id = request('dishes_category_id',0);
        $dishesGoods->intro = request('intro', '');
        $dishesGoods->detail_image = request('detail_image', '');
        $dishesGoods->status = request('status', 1);
        $dishesGoods->is_hot = request('is_hot', 0);
        $dishesGoods->sort = DishesGoods::max('sort') + 1;

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
        $dishesGoodsList = DishesGoods::where('merchant_id', $dishesGoods->merchant_id)
            ->where('name', request('name'))
            ->where('name', '<>', $dishesGoods->name)
            ->get();
        if (count($dishesGoodsList) > 0){
            throw new BaseResponseException('商品名称重复！');
        }

        $dishesGoods->name = request('name');
        $dishesGoods->market_price = request('market_price', 0);
        $dishesGoods->sale_price = request('sale_price', 0);
        $dishesGoods->dishes_category_id = request('dishes_category_id', 0);
        $dishesGoods->intro = request('intro', '');
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

    /**
     * 单品排序
     */
    public function saveOrder(){
        $type = request('type');
        $categoryId = request('category_id', '');
        if ($type == 'down'){
            $option = '<';
            $order = 'desc';
        }else{
            $option = '>';
            $order = 'asc';
        }

        $dishesGoods = DishesGoods::findOrFail(request('id'));
        if (empty($dishesGoods)){
            throw new BaseResponseException('该单品不存在');
        }
        $dishesGoodsExchange = DishesGoods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('sort', $option, $dishesGoods['sort'])
            ->when($categoryId, function (Builder $query) use ($categoryId) {
                $query->where('dishes_category_id', $categoryId);
            })
            ->orderBy('sort', $order)
            ->first();
        if (empty($dishesGoodsExchange)){
            throw new BaseResponseException('交换位置的单品不存在');
        }

        $item = $dishesGoods['sort'];
        $dishesGoods['sort'] = $dishesGoodsExchange['sort'];
        $dishesGoodsExchange['sort'] = $item;

        try{
            DB::beginTransaction();
            $dishesGoods->save();
            $dishesGoodsExchange->save();
            DB::commit();
            return Result::success();
        }catch (\Exception $e){
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }

}