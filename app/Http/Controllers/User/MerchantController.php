<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class MerchantController extends Controller
{

    public function getList()
    {
        $keyword = request('keyword');
        $merchant_category_id = request('merchant_category_id');
        $lng = request('lng');
        $lat = request('lat');
        $data = Merchant::when($keyword, function (Builder $query) use ($keyword){
            $query->where('name', 'like', $keyword);
        })->when($merchant_category_id, function (Builder $query) use ($merchant_category_id) {
            $query->where('merchant_category_id', $merchant_category_id);
        })->when($lng && $lat, function (Builder $query) use ($lng, $lat) {

        })->paginate();
        return Result::success(['list' => $data->items(), 'total' => $data->total()]);
    }

    public function getGoods()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
        $merchant_id = request('merchant_id');
        $list = Goods::where('merchant_id', $merchant_id)->get();
        $list->each(function ($item) {
            $item->pic_list = explode(',', $item);
        });
        return Result::success(['list' => $list]);
    }
}