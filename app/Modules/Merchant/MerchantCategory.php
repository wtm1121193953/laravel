<?php

namespace App\Modules\Merchant;

use App\BaseModel;

/**
 * Class MerchantCategory
 * @package App\Modules\Merchant
 *
 * @property int    pid
 * @property string name
 * @property string icon
 * @property int status
 *
 */

class MerchantCategory extends BaseModel
{
    const STATUS_ON = 1;
    const STATUS_OFF = 2;

}
