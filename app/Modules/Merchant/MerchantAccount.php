<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Modules\User\GenPassword;

/**
 * Class MerchantAccount
 * @package App\Modules\Merchant
 *
 * @property int    oper_id
 * @property int    merchant_id
 * @property int    merchant_category_id
 * @property string name
 * @property string account
 * @property string mobile
 * @property string email
 * @property string password
 * @property string salt
 * @property int status
 * @property int type
 *
 */
class MerchantAccount extends BaseModel
{
    use GenPassword;
    //
    const  TYPE_NORMAL = 1;
    const TYPE_CS = 2;
}
