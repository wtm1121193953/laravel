<?php

namespace App\Modules\FilterKeyword;

use App\BaseModel;

/**
 * Class FilterKeyword
 * @package App\Modules\FilterKeyword
 *
 * @property string keyword
 * @property number status
 * @property number category_number
 */
class FilterKeyword extends BaseModel
{
    //
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    // 过滤关键词的数值
    const CATEGORY_GOODS_NAME = 1;   //团购商品名称
    const CATEGORY_DISHES_GOODS_NAME = 2;    //单品名称
    const CATEGORY_DISHES_CATEGORY_NAME = 4;     //单品分类名称
}
