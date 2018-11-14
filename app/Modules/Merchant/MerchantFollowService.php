<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\DataCacheService;
use App\Exceptions\BaseResponseException;
use App\Modules\Goods\GoodsService;
use App\Modules\Oper\Oper;
use App\Support\Lbs;
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;


/**
 * 商户关注相关service
 * Class MerchantFollowService
 * @package App\Modules\Merchant
 */
class MerchantFollowService extends BaseService
{
    /**
     * 修改商户关注状态
     * @param $params
     * @return int
     */
    public static function modifyFollows($params)
    {
        $status = array_get($params, "status");
        $userId = array_get($params,'user_id');
        $merchantId = array_get($params,'merchant_id');

        $merchant = new Merchant();
        $merchantFollowQuery = MerchantFollow::where('user_id',$userId)->where('merchant_id',$merchantId)->first();
        if($status ==1){ //未关注，增加记录

            if($merchantFollowQuery){

                if($merchantFollowQuery->status == MerchantFollow::USER_NOT_FOLLOW){
                    $merchantFollowQuery->status = MerchantFollow::USER_YES_FOLLOW;
                    $merchantFollowQuery->save();
                }else{
                    throw new BaseResponseException('已关注');
                }

            }else{
                $merchantFollow = new MerchantFollow();
                $merchantFollow->merchant_id = $merchantId;
                $merchantFollow->user_id = $userId;
                $merchantFollow->status = MerchantFollow::USER_YES_FOLLOW;
                $merchantFollow->save();
            }

            $merchant->where('id',$merchantId)->increment('user_follows');
            $follow_status = MerchantFollow::USER_YES_FOLLOW; //返回已关注状态
        }else{

            if($merchantFollowQuery->status == MerchantFollow::USER_NOT_FOLLOW){
                throw new BaseResponseException('已取消');
            }else{
                $merchantFollowQuery->status = MerchantFollow::USER_NOT_FOLLOW;
                $merchantFollowQuery->save();
            }
            $merchant->where('id',$merchantId)->decrement('user_follows');
            $follow_status = MerchantFollow::USER_NOT_FOLLOW; //返回未关注状态
        }
        return $follow_status;
    }

    /**
     * 获取用户关注列表
     * @param $userId
     * @param $lng
     * @param $lat
     * @param bool $onlyPayToPlatform
     * @return array
     */
    public static function getFollowMerchantList($userId,$lng,$lat,$onlyPayToPlatform =false)
    {
        $query = Merchant::where('status', 1)
            ->where('oper_id', '>', 0)
            ->whereHas('merchantFollow', function (Builder $query) use ($userId) {
                $query->where('user_id',$userId)
                    ->where('status',MerchantFollow::USER_YES_FOLLOW);
            })
            ->when($onlyPayToPlatform, function (Builder $query) {
                $query->whereHas('oper', function(Builder $query){
                    $query->whereIn('pay_to_platform', [ Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING ]);
                });
            })
            ->whereIn('audit_status', [Merchant::AUDIT_STATUS_SUCCESS, Merchant::AUDIT_STATUS_RESUBMIT]);

        if($lng && $lat){
            // 如果是按距离搜索, 需要在程序中按距离排序
            $allList = $query->select('id','is_pilot')->get();
            $total = $query->count();
            $list = $allList->map(function ($item) use ($lng, $lat, $userId) {
                $item->distance = Lbs::getDistanceOfMerchant($item->id, $userId, floatval($lng), floatval($lat));

                if ($item->is_pilot == 1) {
                    $item->distance += 100000000;
                }
                return $item;
            })
                ->sortBy('distance')
                ->forPage(request('page', 1), 15)
                ->values()
                ->map(function($item) {
                    if ($item->is_pilot == 1) {
                        $item->distance -= 100000000;
                    }
                    $item->distance = Utils::getFormativeDistance($item->distance);
                    $merchant = DataCacheService::getMerchantDetail($item->id);
                    $merchant->distance = $item->distance;
                    // 格式化距离
                    return $merchant;
                });
        }else {
            // 没有按距离搜索时, 直接在数据库中排序并分页
            $query->orderBy('is_pilot', 'asc');
            $data = $query->paginate();
            $list = $data->items();
            $total = $data->total();
        }

        // 补充商家其他信息
        $list = collect($list);
        $list->each(function ($item){
            $item->desc_pic_list = $item->desc_pic_list ? explode(',', $item->desc_pic_list) : [];
            if($item->business_time) $item->business_time = json_decode($item->business_time, 1);
            $category = MerchantCategory::find($item->merchant_category_id);
            $item->merchantCategoryName = $category->name;
            // 最低消费
            $item->lowestAmount = MerchantService::getLowestPriceForMerchant($item->id);
            // 兼容v1.0.0版客服电话字段
            $item->contacter_phone = $item->service_phone;
            // 商户评级字段，暂时全部默认为5星
            $item->grade = 5;
            // 首页商户列表，显示价格最低的n个团购商品
            $item->lowestGoods = GoodsService::getLowestPriceGoodsForMerchant($item->id, 2);
        });

        return ['list' => $list, 'total' => $total];

    }
}