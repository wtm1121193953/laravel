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
 *
 */
class MerchantAccount extends BaseModel
{
    use GenPassword;
    //
}
