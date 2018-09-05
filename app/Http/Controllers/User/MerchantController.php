<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Area\Area;
use App\Modules\Goods\GoodsService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantCategoryService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Setting\SettingService;
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

        $checkVersion = false;
        if (isset($_SERVER['HTTP_X_VERSION'])) {
            $miniprogramVersion = $_SERVER['HTTP_X_VERSION'];
            $checkVersion = $miniprogramVersion < 'v1.4.0';
        }

        // 暂时去掉商户列表中的距离限制
        $radius = request('radius');
        $radius = $radius == 200000 ? 0 : $radius;
        // 价格搜索
        $lowestPrice = request('lowest_price', 0);
        $highestPrice = request('highest_price', 0);
        if ($lowestPrice && $highestPrice && $lowestPrice > $highestPrice){
            throw new BaseResponseException('搜索条件的最低价格不能高于最高价格');
        }

        $distances = null;
        if($lng && $lat && $radius){
            // 如果经纬度及范围都存在, 则按距离筛选出附近的商家
            $distances = Lbs::getNearlyMerchantDistanceByGps($lng, $lat, $radius);
        }

        $merchantShareInMiniprogram = SettingService::getValueByKey('merchant_share_in_miniprogram');

        $currentOperId = request()->get('current_oper')->id;
        $query = Merchant::when($merchantShareInMiniprogram != 1, function(Builder $query) use ($currentOperId) {
                $query->where('oper_id', $currentOperId);
            })
            ->where('oper_id', '>', 0)
            ->where('status', 1)
            ->whereIn('audit_status', [Merchant::AUDIT_STATUS_SUCCESS, Merchant::AUDIT_STATUS_RESUBMIT])
            ->when($city_id, function(Builder $query) use ($city_id){
                // 特殊城市，如澳门。属于省份，要显示下属所有城市的商户
                $areaInfo = Area::where('area_id', $city_id)->where('path', 1)->first();
                if (empty($areaInfo)) {
                    $query->where('city_id', $city_id);
                } else {
                    $cityIdArray = Area::where('parent_id', $city_id)
                        ->where('path', 2)
                        ->select('area_id')
                        ->get()
                        ->pluck('area_id');
                    $query->whereIn('city_id', $cityIdArray);
                }
            })
            ->when(!$merchant_category_id && $keyword, function(Builder $query) use ($keyword){
                // 不传商家类别id且关键字存在时, 若关键字等同于类别, 则搜索该类别以及携带该关键字的商家
                $category = MerchantCategory::where('name', $keyword)->first();
                if($category){
                    $query->where(function(Builder $query) use ($keyword,$category) {
                            $query->where('merchant_category_id', $category->id)
                                ->orWhere('name', 'like', "%$keyword%")
                                ->orWhere('signboard_name', 'like', "%$keyword%");
                        });
                }else {
                    $query->where(function (Builder $query) use ($keyword){
                        $query->where('name', 'like', "%$keyword%")
                            ->orWhere('signboard_name', 'like', "%$keyword%");
                    });
                }
            })
            ->when($merchant_category_id && $keyword, function(Builder $query) use ($merchant_category_id, $keyword){
                // 如果传了类别及关键字, 则类别和关键字都搜索
                $merchantCategorySubArray = MerchantCategoryService::getSubCategoryIds($merchant_category_id);
                $query->where(function (Builder $query) use ($keyword) {
                        $query->where('name', 'like', "%$keyword%")
                            ->orWhere('signboard_name', 'like', "%$keyword%");
                    })
                    ->when($merchantCategorySubArray, function (Builder $query) use ($merchantCategorySubArray) {
                        $query->whereIn('merchant_category_id', $merchantCategorySubArray);
                    })
                    ->when(!$merchantCategorySubArray, function (Builder $query) use ($merchant_category_id) {
                        $query->where('merchant_category_id', $merchant_category_id);
                    });
            })
            ->when($merchant_category_id && empty($keyword), function(Builder $query) use ($merchant_category_id, $keyword){
                // 如果只传了类别, 没有关键字
                $merchantCategorySubArray = MerchantCategoryService::getSubCategoryIds($merchant_category_id);
                $query->when($merchantCategorySubArray, function (Builder $query) use ($merchantCategorySubArray) {
                        $query->whereIn('merchant_category_id', $merchantCategorySubArray);
                    })
                    ->when(!$merchantCategorySubArray, function (Builder $query) use ($merchant_category_id) {
                        $query->where('merchant_category_id', $merchant_category_id);
                    });
            })
            ->when($lng && $lat && $radius, function (Builder $query) use ($distances) {
                // 如果范围存在, 按距离搜索, 并按距离排序
                $query->whereIn('id', array_keys($distances));
            })
            ->when($lowestPrice || $highestPrice, function (Builder $query) use ($lowestPrice, $highestPrice){
                // 有价格限制时 按照价格区间筛选 并按照价格排序
                $query->when($lowestPrice && !$highestPrice, function (Builder $query) use ($lowestPrice) {
                        $query->where('lowest_amount', '>=', $lowestPrice);
                    })
                    ->when($highestPrice, function (Builder $query) use ($lowestPrice, $highestPrice) {
                        $query->where('lowest_amount', '>=', $lowestPrice)
                            ->where('lowest_amount', '<', $highestPrice);
                    })
                    ->orderBy('lowest_amount');
            })
            ->when($checkVersion, function (Builder $query) {
                $query->where('is_pilot', Merchant::NORMAL_MERCHANT);
            });

        if($lng && $lat){
            // 如果是按距离搜索, 需要在程序中按距离排序
            $allList = $query->get();
            $total = $query->count();
            $list = $allList->map(function ($item) use ($lng, $lat) {
                $item->distance = Lbs::getDistanceOfMerchant($item->id, request()->get('current_open_id'), $lng, $lat);
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
            $item->lowestAmount = MerchantService::getLowestPriceForMerchant($item->id);
            // 判断商户是否是当前小程序关联运营中心下的商户
            $item->isOperSelf = $item->oper_id === $currentOperId ? 1 : 0;
            // 兼容v1.0.0版客服电话字段
            $item->contacter_phone = $item->service_phone;
            // 商户评级字段，暂时全部默认为5星
            $item->grade = 5;
            // 首页商户列表，显示价格最低的n个团购商品
            $item->lowestGoods = GoodsService::getLowestPriceGoodsForMerchant($item->id, 2);
        });

        return Result::success(['list' => $list, 'total' => $total]);
    }

    /**
     * 获取商户详情
     *
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
        //商家是否开启单品模式
        $detail->isOpenDish = MerchantSettingService::getValueByKey($id,'dishes_enabled');
        // 商家是否开启直接买单
        $detail->isOpenQrcodePay = 0;
        // 最低消费
        $detail->lowestAmount = MerchantService::getLowestPriceForMerchant($detail->id);
        $currentOperId = request()->get('current_oper')->id;
        // 判断商户是否是当前小程序关联运营中心下的商户
        $detail->isOperSelf = $detail->oper_id === $currentOperId ? 1 : 0;
        // 兼容v1.0.0版客服电话字段
        $detail->contacter_phone = $detail->service_phone;
        // 商户评级字段，暂时全部默认为5星
        $detail->grade = 5;

        return Result::success(['list' => $detail]);
    }

    /**
     * 格式化距离
     * @param $distance
     * @return string
     */
    private function _getFormativeDistance($distance)
    {
        return $distance >= 1000 ? (number_format($distance / 1000, 1) . 'km') : ($distance . 'm');
    }

}