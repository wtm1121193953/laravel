<?php

namespace App\Modules\Dishes;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DishesGoods
 * @package App\Modules\Dishes
 *
 * @property number oper_id
 * @property number merchant_id
 * @property number market_price
 * @property number sale_price
 * @property number dishes_category_id
 * @property number sell_number
 * @property string name
 * @property string intro
 * @property number is_hot
 * @property string detail_image
 * @property number status
 * @property number sort
 */
class DishesGoods extends BaseModel
{
    //
    use SoftDeletes;

    //商品上下架状态
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    public function dishesCategory()
    {
        return $this->belongsTo(DishesCategory::class);
    }

    /**
     * 初始化新加排序字段sort的数值
     * @author andy
     */
    public static function initSortData(){
        self::chunk(500, function ($dishes) {
            foreach ($dishes as $one) {
                $one->sort = $one->id;
                $one->save();
            }
        });
    }
}
