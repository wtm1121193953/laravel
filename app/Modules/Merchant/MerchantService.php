<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 15:49
 */

namespace App\Modules\Merchant;


use App\BaseService;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Goods\Goods;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizMember;
use App\Support\Lbs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MerchantService extends BaseService
{

    public static function getAllNames(array $data)
    {
        // todo 获取所有的商户名, 可以传入查询条件, 后期可以对查询结果做缓存
    }

    /**
     * 查询商户列表
     * @param array $data 查询条件 {
     *      id,
     *      operId,
     *      creatorOperId,
     *      name,
     *      signboardName,
     *      auditStatus,
     *      startCreatedAt,
     *      endCreatedAt,
     *  }
     * @param bool $getWithQuery
     * @return Merchant|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $data, bool $getWithQuery=false)
    {
        $id = $data['id'];
        $operId = $data['operId'];
        $name = $data['name'];
        $signboardName = $data['signboardName'];
        $auditStatus = $data['auditStatus'];
        $startCreatedAt = $data['startCreatedAt'];
        $endCreatedAt = $data['endCreatedAt'];

        // 全局限制条件
        $query = Merchant::where('audit_oper_id', '>', 0)->orderByDesc('id');

        // 筛选条件
        if($id){
            $query->where('id', $id);
        }
        if($operId){
            if(is_array($operId) || $operId instanceof Collection){
                $query->where(function (Builder $query) use ($operId) {
                    $query->whereIn('oper_id',  $operId)
                        ->orWhereIn('audit_oper_id', $operId);
                });
            }else {
                $query->where(function (Builder $query) use ($operId) {
                    $query->where('oper_id',  $operId)
                        ->orWhere('audit_oper_id', $operId);
                });
            }
        }
        if(!empty($auditStatus)){
            if(is_array($auditStatus) || $auditStatus instanceof Collection){
                $query->whereIn('audit_status', $auditStatus);
            }else {
                $query->where('audit_status', $auditStatus);
            }
        }
        if($startCreatedAt && $endCreatedAt){
            if($startCreatedAt==$endCreatedAt){
                $query->whereDate('created_at', $startCreatedAt);
            }else{
                $query->whereBetween('created_at', [$startCreatedAt . ' 00:00:00', $endCreatedAt . ' 23:59:59']);
            }
        }else if($startCreatedAt){
            $query->where('created_at', '>=', $startCreatedAt . ' 00:00:00');
        }else if($endCreatedAt){
            $query->where('created_at', '<=', $endCreatedAt . ' 23:59:59');
        }
        if($name){
            $query->where('name', 'like', "%$name%");
        }
        if($signboardName){
            $query->where('signboard_name', 'like', "%$signboardName%");
        }

        if($getWithQuery){
            return $query;
        }else {

            $data = $query->paginate();

            $data->each(function ($item) {
                $item->categoryPath = MerchantCategoryService::getCategoryPath($item->merchant_category_id);
                $item->business_time = json_decode($item->business_time, 1);
                $item->operName = Oper::where('id', $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id)->value('name');
                $item->operId = $item->oper_id > 0 ? $item->oper_id : $item->audit_oper_id;
                $item->operBizMemberName = OperBizMember::where('oper_id', $item->operId)->where('code', $item->oper_biz_member_code)->value('name') ?: '';
            });

            return $data;
        }
    }

    /**
     * 根据ID获取商户详情
     * @param $id
     * @return Merchant
     */
    public static function detail($id)
    {

        $merchant = Merchant::findOrFail($id);
        $merchant->categoryPath = MerchantCategoryService::getCategoryPath($merchant->merchant_category_id);
        $merchant->business_time = json_decode($merchant->business_time, 1);
        $merchant->operName = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->value('name');
        $merchant->creatorOperName = Oper::where('id', $merchant->creator_oper_id)->value('name');
        $merchant->desc_pic_list = $merchant->desc_pic_list ? explode(',', $merchant->desc_pic_list) : '';
        $merchant->contract_pic_url = $merchant->contract_pic_url ? explode(',', $merchant->contract_pic_url) : '';
        $merchant->other_card_pic_urls = $merchant->other_card_pic_urls ? explode(',', $merchant->other_card_pic_urls) : '';
        $merchant->bank_card_pic_a = $merchant->bank_card_pic_a ? explode(',', $merchant->bank_card_pic_a) : '';
        if($merchant->oper_biz_member_code){
            $merchant->operBizMemberName = OperBizMember::where('code', $merchant->oper_biz_member_code)->value('name');
        }
        $oper = Oper::where('id', $merchant->oper_id > 0 ? $merchant->oper_id : $merchant->audit_oper_id)->first();
        if ($oper){
            $merchant->operAddress = $oper->province.$oper->city.$oper->area.$oper->address;
        }
        return $merchant;
    }

    public static function audit()
    {
        // todo 审核商户
    }

    public static function edit()
    {
        // todo 编辑商户信息
    }

    public static function add()
    {
        // todo 添加商户信息
    }

    public static function addFromDraft()
    {
        // todo 从草稿箱添加
    }

    public static function addFromMerchantPool()
    {
        // todo 从商户池添加
    }


    /**
     * 同步商户地区数据到Redis中
     * @param $merchant Collection|Merchant
     */
    public static function geoAddToRedis($merchant)
    {
        if($merchant instanceof Collection){
            Lbs::merchantGpsAdd($merchant);
        }else {
            Lbs::merchantGpsAdd($merchant->id, $merchant->lng, $merchant->lat);
        }
    }

    /**
     * 同步所有商户的LBS数据到Redis中
     */
    public static function geoAddAllToRedis()
    {
        Merchant::chunk(100, function (Collection $list){
            self::geoAddToRedis($list);
        });
    }


    /**
     * 更新商户的最低价格
     * @param $merchant_id
     */
    public static function updateMerchantLowestAmount($merchant_id)
    {
        $merchant = Merchant::findOrFail($merchant_id);
        $merchant->lowest_amount = self::getLowestPriceForMerchant($merchant_id);
        $merchant->save();
    }

    /**
     * 根据商家获取最低价商品的价格, 没有商品是返回null
     * @param $merchantId
     * @return number|null
     */
    public static function getLowestPriceForMerchant($merchantId)
    {
        $goodsLowestAmount = Goods::where('merchant_id', $merchantId)
                ->where('status', Goods::STATUS_ON)
                ->orderBy('price')
                ->value('price') ?? 0;
        $dishesGoodsLowestAmount = DishesGoods::where('merchant_id', $merchantId)
                ->where('status', DishesGoods::STATUS_ON)
                ->orderBy('sale_price')
                ->value('sale_price') ?? 0;

        return min($goodsLowestAmount, $dishesGoodsLowestAmount);
    }

}