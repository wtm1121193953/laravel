<?php

namespace App\Modules\Goods;

use App\BaseModel;
use App\Modules\Dishes\DishesGoods;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goods extends BaseModel
{
    use SoftDeletes;
    //

    //商品上下架状态
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    /**
     * 根据商家获取最低价商品的价格, 没有商品是返回null
     * @param $merchantId
     * @return number|null
     */
    public static function getLowestPriceForMerchant($merchantId)
    {
        $goodsLowestAmount = Goods::where('merchant_id', $merchantId)
            ->where('status', 1)
            ->orderBy('price')
            ->value('price');
        $dishesGoodsLowestAmount = DishesGoods::where('merchant_id', $merchantId)
            ->where('status', 1)
            ->orderBy('sale_price')
            ->value('sale_price');
        $lowestAmount = $goodsLowestAmount > $dishesGoodsLowestAmount ? $dishesGoodsLowestAmount : $goodsLowestAmount;
        return $lowestAmount;
    }
}
