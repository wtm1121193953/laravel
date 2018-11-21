<?php

namespace App\Modules\Cs;

use App\BaseModel;

/**
 * Class CsMerchantCategory
 * @package App\Modules\Cs
 * @property integer cs_merchant_id
 * @property integer platform_category_id
 * @property  string cs_cat_name
 * @property integer cs_category_parent_id
 * @property integer cs_category_level
 * @property integer platform_cat_status
 * @property integer status
 * @property integer sort
 */
class CsMerchantCategory extends BaseModel
{
    //

    const STATUS_ON = 1;//上架
    const STATUS_OFF = 2;//下架

    protected $guarded = [];
}
