<?php

namespace App\Modules\Goods;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goods extends BaseModel
{
    use SoftDeletes;
    //

    /**
     * 根据商家获取最低价商品的价格, 没有商品是返回null
     * @param $merchantId
     * @return number|null
     */
    public static function getLowestPriceForMerchant($merchantId)
    {
        $lowestAmount = Goods::where('merchant_id', $merchantId)
            ->where('status', 1)
            ->orderBy('price')
            ->value('price');
        return $lowestAmount;
    }
}
