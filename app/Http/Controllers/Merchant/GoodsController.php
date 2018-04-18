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
        $data = Goods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

        $data->each(function($item){
            $item->pic_list = $item->pic_list ? explode(',', $item->pic_list) : [];
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $goods = Goods::findOrFail($id);
        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];
        return Result::success($goods);
    }

    /**
     * 获取全部列表
     */
    public function getAllList()
    {
        $status = request('status');
        $list = Goods::where('merchant_id', request()->get('current_user')->merchant_id)
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
            'market_price' => 'required',
            'price' => 'required',
        ]);
        $goods = new Goods();
        $goods->oper_id = request()->get('current_user')->oper_id;
        $goods->merchant_id = request()->get('current_user')->merchant_id;
        $goods->name = request('name');
        $goods->market_price = request('market_price', 0);
        $goods->price = request('price', 0);
        $goods->start_date = request('start_date');
        $goods->end_date = request('end_date');
        $goods->pic = request('pic', '');
        $picList = request('pic_list', '');
        if(is_array($picList)){
            $picList = implode(',', $picList);
        }
        $goods->pic_list = $picList;

        $goods->thumb_url = request('thumb_url', '');
        $goods->desc = request('desc', '');
        $goods->buy_info = request('buy_info', '');
        $goods->status = request('status', 1);

        $goods->save();

        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];
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
            'market_price' => 'required',
            'price' => 'required',
        ]);
        $goods = Goods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('id', request('id'))
            ->firstOrFail();
        $goods->oper_id = request()->get('current_user')->oper_id;
        $goods->merchant_id = request()->get('current_user')->merchant_id;
        $goods->name = request('name');
        $goods->market_price = request('market_price', 0);
        $goods->price = request('price', 0);
        $goods->start_date = request('start_date');
        $goods->end_date = request('end_date');
        $goods->pic = request('pic', '');
        $picList = request('pic_list', '');
        if(is_array($picList)){
            $picList = implode(',', $picList);
        }
        $goods->pic_list = $picList;

        $goods->thumb_url = request('thumb_url', '');
        $goods->desc = request('desc', '');
        $goods->buy_info = request('buy_info', '');
        $goods->status = request('status', 1);

        $goods->save();
        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];

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
        $goods = Goods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('id', request('id'))
            ->firstOrFail();
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
        $goods = Goods::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('id', request('id'))
            ->firstOrFail();
        $goods->delete();
        return Result::success($goods);
    }

}