<?php
namespace App\Modules\Dishes;

use App\BaseService;
use App\Modules\Merchant\Merchant;
use Illuminate\Database\Eloquent\Builder;


class DishesService extends BaseService
{
    /**
     * 获取单品指定分类的商品
     * @param $merchantId
     * @return DishesCategory[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getDishesCategory($merchantId)
    {
        $categorys =DishesCategory::whereHas('dishesGoods', function (Builder $query) {
            $query->where('status', DishesGoods::STATUS_ON);
        })
            ->where('merchant_id', $merchantId)
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->get();

        return $categorys;
    }

    /**
     * 点菜添加
     * @param $merchantId
     * @param $userId
     * @param $dishesList
     * @return Dishes
     */
    public static function addDishes($merchantId,$userId,$dishesList)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $dishes = new Dishes();
        $dishes->oper_id = $merchant->oper_id;
        $dishes->merchant_id = $merchant->id;
        $dishes->user_id = $userId;
        $dishes->save();

        foreach ($dishesList as $item){
            $dishesGoods = DishesGoods::findOrFail($item['id']);
            if ($dishesGoods['oper_id'] !== $merchant->oper_id){
                continue;
            }
            $dishesItem = new DishesItem();
            $dishesItem->oper_id = $merchant->oper_id;
            $dishesItem->merchant_id = $merchant->id;
            $dishesItem->dishes_id = $dishes->id;
            $dishesItem->user_id = $userId;
            $dishesItem->dishes_goods_id = $item['id'];
            $dishesItem->number = $item['number'];
            $dishesItem->dishes_goods_sale_price = $dishesGoods['sale_price'];
            $dishesItem->dishes_goods_detail_image = $dishesGoods['detail_image'];
            $dishesItem->dishes_goods_name = $dishesGoods['name'];
            $dishesItem->save();
        }

        return $dishes;
    }

    /**
     * 点菜的菜单详情
     * @param $dishesId
     * @return array
     */
    public static function detailDishes($dishesId)
    {
        $list = DishesItem::where('dishes_id',$dishesId)->get();
        $list->each(function($item){
            $item->total_price = $item->number * $item->dishes_goods_sale_price;
        });

        return $list;
    }

    /**
     * 通过单品id获取订单单品列表
     * @param $dishes_id
     * @return DishesItem[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getDishesItemsByDishesId($dishes_id)
    {
        $dishesItems = DishesItem::where('dishes_id', $dishes_id)->get();

        return $dishesItems;
    }
}