<?php

namespace App\Modules\Cs;

use App\BaseModel;

/**
 * Class CsPlatformCategory
 * @package App\Modules\Cs
 * @property string cat_name
 * @property int status
 * @property int parent_id
 * @property int level
 *
 */
class CsPlatformCategory extends BaseModel
{
    //
    const STATUS_ON = 1;//启用
    const STATUS_OFF = 2;//暂停
}
