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
use App\Modules\Setting\SettingService;
use App\Result;
use App\Support\Lbs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Psy\Util\Json;

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

        $merchantShareInMiniprogram = SettingService::getValueByKey('merchant_share_in_miniprogram');

        $currentOperId = request()->get('current_oper')->id;
        $query = Merchant
            ::when($merchantShareInMiniprogram != 1, function(Builder $query) use ($currentOperId) {
                $query->where('oper_id', $currentOperId);
            })
            ->where('oper_id', '>', 0)
            ->where('status', 1)
            ->whereIn('audit_status', [Merchant::AUDIT_STATUS_SUCCESS, Merchant::AUDIT_STATUS_RESUBMIT])
            ->when($city_id, function(Builder $query) use ($city_id){
                $query->where('city_id', $city_id);
            })
            ->when(!$merchant_category_id && $keyword, function(Builder $query) use ($keyword){
                // 不传商家类别id且关键字存在时, 若关键字等同于类别, 则搜索该类别以及携带该关键字的商家
                $category = MerchantCategory::where('name', $keyword)->first();
                if($category){
                    $query->where('merchant_category_id', $category->id)
                        ->orWhere('name', 'like', "%$keyword%");
                }else {
                    $query->where('name', 'like', "%$keyword%");
                }
            })
            ->when($merchant_category_id && $keyword, function(Builder $query) use ($merchant_category_id, $keyword){
                // 如果传了类别及关键字, 则类别和关键字都搜索
                $query->where('merchant_category_id', $merchant_category_id)
                    ->where('name', 'like', "%$keyword%");
            })
            ->when($merchant_category_id && empty($keyword), function(Builder $query) use ($merchant_category_id, $keyword){
                // 如果只传了类别, 没有关键字
                $query->where('merchant_category_id', $merchant_category_id);
            })
            ->when($lng && $lat && $radius, function (Builder $query) use ($distances) {
                // 如果范围存在, 按距离搜索, 并按距离排序
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
                ->sortBy('distance')
                ->forPage(request('page', 1), 15)
                ->values()
                ->each(function($item) {
                    // 格式化距离
                    $item->distance = $this->_getFormativeDistance($item->distance);
                });
        }else {
            // 没有按距离搜索时, 直接在数据库中排序并分页
            $data = $query->paginate();
            // 如果传递了经纬度信息, 需要计算用户与商家之间的距离
            if($lng && $lat){
                $data->each(function ($item) use ($lng, $lat){
                    $distance = Lbs::getDistanceOfMerchant($item->id, request()->get('current_open_id'), $lng, $lat);
                    // 格式化距离
                    $item->distance = $this->_getFormativeDistance($distance);
                });
            }
            $list = $data->items();
            $total = $data->total();
        }

        // 补充商家其他信息
        $list = collect($list);
        $list->each(function ($item) use ($currentOperId) {
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            if($item->business_time) $item->business_time = json_decode($item->business_time, 1);
            $category = MerchantCategory::find($item->merchant_category_id);
            $item->merchantCategoryName = $category->name;
            // 最低消费
            $item->lowestAmount = Goods::getLowestPriceForMerchant($item->id);
            // 判断商户是否是当前小程序关联运营中心下的商户
            $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
            // 兼容v1.0.0版客服电话字段
            $item->contacter_phone = $item->service_phone;
        });

        return Result::success(['list' => $list, 'total' => $total]);
    }

    /**
     * 获取商户详情
     *
     * TODO  menchant settting
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $lng = request('lng');
        $lat = request('lat');

        $detail = Merchant::findOrFail($id);
        $detail->desc_pic_list = $detail->desc_pic_list ? explode(',', $detail->desc_pic_list) : [];
        if($detail->business_time) $detail->business_time = json_decode($detail->business_time, 1);
        if($lng && $lat){
            $distance = Lbs::getDistanceOfMerchant($id, request()->get('current_open_id'), $lng, $lat);
            // 格式化距离
            $detail->distance = $this->_getFormativeDistance($distance);
        }
        $category = MerchantCategory::find($detail->merchant_category_id);
        $detail->merchantCategoryName = $category->name;
        // 最低消费
        $detail->lowestAmount = Goods::getLowestPriceForMerchant($detail->id);
        $currentOperId = request()->get('current_oper')->id;
        // 判断商户是否是当前小程序关联运营中心下的商户
        $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        // 兼容v1.0.0版客服电话字段
        $detail->contacter_phone = $detail->service_phone;

        return Result::success(['list' => $detail]);
    }

    private function _getFormativeDistance($distance)
    {
        return $distance >= 1000 ? (number_format($distance / 1000, 1) . '千米') : ($distance . '米');
    }

}