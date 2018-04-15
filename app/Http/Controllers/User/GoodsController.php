<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 13:33
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Result;

class GoodsController extends Controller
{

    public function getList()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
        $merchant_id = request('merchant_id');
        $list = Goods::where('merchant_id', $merchant_id)->get();
        $list->each(function ($item) {
            $item->pic_list = explode(',', $item->pic_list);
            $item->sell_number = 200;     //TODO 没有这个字段，目前供测试使用
        });
        return Result::success(['list' => $list]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $detail = Goods::findOrFail(request('id'));
        $detail->pic_list = explode(',', $detail->pic_list);
        $detail->sell_number = 200;     //TODO 没有这个字段，目前供测试使用

        return Result::success($detail);
    }
}