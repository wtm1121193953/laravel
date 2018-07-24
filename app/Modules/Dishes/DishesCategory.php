<?php

namespace App\Modules\Dishes;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DishesCategory
 * @package App\Modules\Dishes
 *
 * @property number user_id
 * @property number oper_id
 * @property number merchant_id
 * @property string name
 * @property number sort
 * @property number status
 */
class DishesCategory extends BaseModel
{
    //
    use SoftDeletes;

    public function dishesGoods()
    {
        return $this->hasMany('App\Modules\Dishes\DishesGoods');
    }

}
