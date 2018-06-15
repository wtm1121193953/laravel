<?php

namespace App\Modules\Dishes;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DishesCategory extends BaseModel
{
    //
    use SoftDeletes;

    public function dishgood()
    {

        return $this->hasMany('App\Modules\Dishes\DishesGoods');
    }

}
