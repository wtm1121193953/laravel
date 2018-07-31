<?php

namespace App\Modules\Area;

use App\BaseModel;

/**
 * Class Area
 * @package App\Modules\Area
 *
 * @property number area_id
 * @property number type
 * @property string name
 * @property number path
 * @property string area_code
 * @property string spell
 * @property string letter
 * @property string first_letter
 * @property number status
 * @property number parent_id
 */
class Area extends BaseModel
{
    public $timestamps = false;
    //

    public static function getNameByAreaId($areaId)
    {
        return Area::where('area_id', $areaId)->value('name');
    }
}
