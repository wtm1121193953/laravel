<?php

namespace App\Modules\FeeSplitting;

use App\BaseModel;

class FeeSplittingRecord extends BaseModel
{
    //
    const TYPE_TO_SELF = 1; // 自返
    const TYPE_TO_PARENT = 2; // 返利给上级
    const TYPE_TO_OPER = 3; // 返利给运营中心
}
