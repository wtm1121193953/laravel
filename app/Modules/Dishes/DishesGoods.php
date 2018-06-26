<?php

namespace App\Modules\Dishes;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
