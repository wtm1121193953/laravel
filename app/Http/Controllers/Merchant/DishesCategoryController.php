<?php

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesCategory;
use App\Modules\Dishes\DishesGoods;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DishesCategoryController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $pageSize = request('pageSize');
        $data = DishesCategory::where('merchant_id', request()->get('current_user')->merchant_id)
            ->when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('sort', 'asc')->paginate($pageSize);

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
        $list = DishesCategory::where('merchant_id', request()->get('current_user')->merchant_id)
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
        $dishesCategory = new DishesCategory();
        $dishesCategory->oper_id = request()->get('current_user')->oper_id;
        $dishesCategory->merchant_id = request()->get('current_user')->merchant_id;
        $dishesCategory->name = request('name');
        $dishesCategory->status = request('status', 1);
        $dishesCategory->sort = DishesCategory::max('sort') + 1;

        $dishesCategory->save();

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
        $dishesCategory = DishesCategory::findOrFail(request('id'));
        $dishesCategory->name = request('name');
        $dishesCategory->status = request('status', 1);

        $dishesCategory->save();

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
        $dishesCategory = DishesCategory::findOrFail(request('id'));
        $dishesCategory->status = request('status');

        $dishesCategory->save();
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
        $goodsCount = DishesGoods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('category_id', $categoryId)->count();
        if ($goodsCount > 0){
            throw new BaseResponseException('该分类下有'.$goodsCount.'个单品，不能删除！');
        }
        $dishesCategory = DishesCategory::findOrFail($categoryId);
        $dishesCategory->delete();
        return Result::success($dishesCategory);
    }

    /**
     * 上下移动
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function saveOrder()
    {
        $type = request('type');
        if ($type == 'up'){
            $option = '<';
            $order = 'desc';
        }else{
            $option = '>';
            $order = 'asc';
        }

        $dishesCategory = DishesCategory::findOrFail(request('id'));
        if (empty($dishesCategory)){
            throw new BaseResponseException('该单品分类不存在');
        }
        $dishesCategoryExchange = DishesCategory::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('sort', $option, $dishesCategory['sort'])
            ->orderBy('sort', $order)
            ->first();
        if (empty($dishesCategoryExchange)){
            throw new BaseResponseException('交换位置的单品分类不存在');
        }

        $item = $dishesCategory['sort'];
        $dishesCategory['sort'] = $dishesCategoryExchange['sort'];
        $dishesCategoryExchange['sort'] = $item;

        DB::beginTransaction();
        $res1 = $dishesCategory->save();
        $res2 = $dishesCategoryExchange->save();
        if ($res1 && $res2){
            DB::commit();
            return Result::success();
        }else{
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }

}