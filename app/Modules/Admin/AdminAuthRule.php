<?php

namespace App\Modules\Admin;

use App\BaseModel;

/**
 * Class AdminAuthRule
 * @package App\Modules\Admin
 *
 * @property string name
 * @property number level
 * @property string url
 * @property string url_all
 * @property string icon
 * @property number sort
 * @property number status
 * @property number pid
 */

class AdminAuthRule extends BaseModel
{
    //
    const STATUS_ON = 1;
    const STATUS_OFF = 2;
}
