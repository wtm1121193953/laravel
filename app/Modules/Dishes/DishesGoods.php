<?php

namespace App\Modules\Dishes;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DishesGoods extends BaseModel
{
    //
    use SoftDeletes;

    public function category()
    {
        return $this->belongsTo('App\Modules\Dishes\DishesCategory');
    }
}
