<?php

namespace App\Modules\Dishes;

use App\BaseModel;


/**
 * Class DishesItem
 * @package App\Modules\Dishes
 *
 * @property number user_id
 * @property number oper_id
 * @property number merchant_id
 * @property number dishes_id
 * @property number dishes_goods_id
 * @property number number
 * @property number dishes_goods_sale_price
 * @property string dishes_goods_detail_image
 * @property string dishes_goods_name
 */
class DishesItem extends BaseModel
{
    //
    public function dishes_goods() {
        return $this->belongsTo(DishesGoods::class);
    }
}
