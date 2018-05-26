<?php

namespace App\Modules\Dishes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DishesGoods extends Model
{
    //
    use SoftDeletes;

    public function category()
    {
        return $this->hasOne('App\Modules\Dishes\DishesCategory');
    }
}
