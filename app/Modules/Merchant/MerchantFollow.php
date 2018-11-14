<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Modules\Oper\Oper;

/**
 * @property integer merchant_id
 * @property integer oper_id
 * @property integer user_id
 * @property int status
 */
class MerchantFollow extends BaseModel
{
    const USER_NOT_FOLLOW = 1; //未关注
    const USER_YES_FOLLOW = 2; //已关注

}
