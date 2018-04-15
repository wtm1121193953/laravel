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
use App\Modules\Merchant\MerchantCategory;
use App\Result;
use App\Support\Lbs;
use Illuminate\Database\Eloquent\Builder;

class MerchantController extends Controller
{

    public function getList()
    {
        $city_id = request('city_id');
        $merchant_category_id = request('merchant_category_id');
        $keyword = request('keyword');
        $lng = request('lng');
        $lat = request('lat');
        $radius = request('radius');

        $distances = null;
        if($lng && $lat && $radius){
            // 如果经纬度及范围都存在, 则按距离筛选出附近的商家
            $distances = Lbs::getNearlyMerchantDistanceByGps($lng, $lat, $radius);
        }

        $query = Merchant::where('status', 1)
            ->whereIn('audit_status', [Merchant::AUDIT_STATUS_SUCCESS, Merchant::AUDIT_STATUS_RESUBMIT])
            ->when($city_id, function(Builder $query) use ($city_id){
                $query->where('city_id', $city_id);
            })
            ->when(!$merchant_category_id && $keyword, function(Builder $query) use ($keyword){
                // 不传商家类别id且关键字存在时, 若关键字等同于类别, 则搜索该类别以及携带该关键字的商家
                $category = MerchantCategory::where('name', $keyword)->first();
                if($category){
                    $query->where('merchant_category_id', $category->id)
                        ->orWhere('keyword', 'like', "%$keyword%");
                }else {
                    $query->where('keyword', 'like', "%$keyword%");
                }
            })
            ->when($merchant_category_id && $keyword, function(Builder $query) use ($merchant_category_id, $keyword){
                // 如果传了类别及关键字, 则类别和关键字都搜索
                $query->where('merchant_category_id', $merchant_category_id)
                    ->where('keyword', 'like', "%$keyword%");
            })
            ->when($lng && $lat && $radius, function (Builder $query) use ($distances) {
                // 如果范围存在, 按距离搜索, 并按距离排序
//                dd(array_keys($distances));
                $query->whereIn('id', array_keys($distances));
            });
        if($lng && $lat && $radius){
            // 如果是按距离搜索, 需要在程序中排序
            $allList = $query->get();
            $total = $query->count();
            $list = $allList->map(function ($item) use ($distances) {
                $item->distance = isset($distances[$item->id]) ? $distances[$item->id] : 10000;
                return $item;
            })
                ->sortBy('distance')->values()
                ->forPage(request('page', 1), 15);
        }else {
            // 没有按距离搜索时, 直接在数据库中排序并分页
            $data = $query->paginate();
            // 如果传递了经纬度信息, 需要计算用户与商家之间的距离
            if($lng && $lat){
                $data->each(function ($item) use ($lng, $lat){
                    $item->distance = Lbs::getDistanceOfMerchant($item->id, request()->get('current_open_id'), $lng, $lat);
                });
            }
            $list = $data->items();
            $total = $data->total();
        }

        // 补充商家其他信息
        $list = collect($list);
        $list->each(function ($item) {
            // 格式化距离
            $item->distance = $item->distance >= 1000 ? (number_format($item->distance / 1000, 1) . '千米') : ($item->distance . '米');
            $category = MerchantCategory::find($item->merchant_category_id);
            $item->merchantCategoryName = $category->name;
            // 最低消费
            $lowestAmount = Goods::where('merchant_id', $item->id)->orderBy('price')->value('price');
            if($lowestAmount > 0){
                $item->lowestAmount = 200;
            }
        });

        return Result::success(['list' => $list, 'total' => $total]);
    }

    /**
     * 获取商户详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $id = request('id');
        $lng = request('lng');
        $lat = request('lat');

        $detail = Merchant::findOrFail($id);
        $detail->distance = Lbs::getDistanceOfMerchant($id, request()->get('current_open_id'), $lng, $lat);
        // 格式化距离
        $detail->distance = $detail->distance >= 1000 ? (number_format($detail->distance / 1000, 1) . '千米') : ($detail->distance . '米');
        $category = MerchantCategory::find($detail->merchant_category_id);
        $detail->merchantCategoryName = $category->name;
        // 最低消费
        $lowestAmount = Goods::where('merchant_id', $id)->orderBy('price')->value('price');
        if($lowestAmount > 0){
            $detail->lowestAmount = 200;
        }

        return Result::success(['list' => $detail]);
    }

}