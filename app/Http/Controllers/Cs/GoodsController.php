<?php

namespace App\Http\Controllers\Cs;


use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGoodService;
use App\Result;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $params = [];
        $data = CsGoodService::getList($params);

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
        $merchantId = request()->get('current_user')->merchant_id;
        $goods = GoodsService::getByIdAndMerchantId($id, $merchantId);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已删除');
        }

        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];

        return Result::success($goods);
    }

    /**
     * 添加数据
     */
    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'market_price' => 'required',
            'price' => 'required',
        ]);


        dd($request);

        return Result::success();
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
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        $merchantId = request()->get('current_user')->merchant_id;
        $goods = GoodsService::editFromRequest(request('id'), $merchantId);

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
        $merchantId = request()->get('current_user')->merchant_id;
        $goods = GoodsService::changeStatus(request('id'), $merchantId, request('status'));

        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];

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
        $goods = GoodsService::del(request('id'), request()->get('current_user')->merchant_id);

        $goods->pic_list = $goods->pic_list ? explode(',', $goods->pic_list) : [];

        return Result::success($goods);
    }

    /**
     * 团购商品排序
     */
    public function saveOrder(){
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $type = request('type', 'up');
        $merchantId = request()->get('current_user')->merchant_id;
        GoodsService::changeSort(request('id'), $merchantId, $type);
        return Result::success();
    }

}