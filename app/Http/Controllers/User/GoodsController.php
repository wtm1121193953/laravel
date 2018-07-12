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
use App\Modules\Merchant\Merchant;
use App\Result;

class GoodsController extends Controller
{

    public function getList()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
        $merchant_id = request('merchant_id');
        $merchant = Merchant::findOrFail($merchant_id);
        $list = Goods::where('merchant_id', $merchant_id)
            ->where('status', 1)->orderBy('sort', 'desc')->get();
        $list->each(function ($item) use ($merchant) {
            $item->pic_list = $item->pic_list ? explode(',', $item->pic_list) : [];
            $item->business_time = json_decode($merchant->business_time, 1);
        });
        return Result::success(['list' => $list]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $detail = Goods::withTrashed()->where('id', request('id'))->firstOrFail();
        $detail->pic_list = $detail->pic_list ? explode(',', $detail->pic_list) : [];
        $merchant = Merchant::findOrFail($detail->merchant_id);
        $detail->business_time = json_decode($merchant->business_time, 1);

        return Result::success($detail);
    }
}