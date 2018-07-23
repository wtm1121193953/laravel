<?php

namespace App\Modules\FilterKeyword;

use App\BaseModel;

class FilterKeyword extends BaseModel
{
    //
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

    // 过滤关键词的数值
    const GOODS_NAME_CATEGORY_NUMBER = 1;   //团购商品名称
    const DISHES_GOODS_NAME_CATEGORY_NUMBER = 2;    //单品名称
    const DISHES_CATEGORY_NAME_CATEGORY_NUMBER = 4;     //单品分类名称
}
