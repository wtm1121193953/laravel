<?php

namespace App\Modules\Goods;

use App\BaseModel;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Merchant\Merchant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Goods
 * @package App\Modules\Goods
 *
 * @property number oper_id
 * @property number merchant_id
 * @property string name
 * @property string desc
 * @property number market_price
 * @property number price
 * @property Carbon start_date
 * @property Carbon end_date
 * @property string thumb_url
 * @property string pic
 * @property string pic_list
 * @property string buy_info
 * @property number status
 * @property string ext_attr
 * @property string tags
 * @property number sort
 * @property number sell_number
 */
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
            ->where('status', self::STATUS_ON)
            ->orderBy('price')
            ->value('price') ?? 0;
        $dishesGoodsLowestAmount = DishesGoods::where('merchant_id', $merchantId)
            ->where('status', self::STATUS_ON)
            ->orderBy('sale_price')
            ->value('sale_price') ?? 0;

        return min($goodsLowestAmount, $dishesGoodsLowestAmount);
    }

    /**
     * 首页商户列表，显示价格最低的n个团购商品
     * @param $merchantId
     * @param $number
     * @return Goods[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getLowestPriceGoodsForMerchant($merchantId, $number)
    {
        $list = Goods::where('merchant_id', $merchantId)
            ->where('status', self::STATUS_ON)
            ->orderBy('sort', 'desc')
            ->limit($number)
            ->get();
        return $list;
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
}
