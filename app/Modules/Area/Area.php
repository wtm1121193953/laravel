<?php

namespace App\Modules\Area;

use App\BaseModel;

class Area extends BaseModel
{
    public $timestamps = false;
    //

    public static function getNameByAreaId($areaId)
    {
        return Area::where('area_id', $areaId)->value('name');
    }
}
