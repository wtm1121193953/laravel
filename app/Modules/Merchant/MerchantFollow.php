<?php

namespace App\Modules\Merchant;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer merchant_id
 * @property integer user_id
 * @property int status
 */
class MerchantFollow extends Model
{
    const USER_NOT_FOLLOW = 1; //未关注
    const USER_YES_FOLLOW = 2; //已关注
}
