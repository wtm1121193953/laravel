<?php

namespace App\Modules\Merchant;

use App\BaseModel;

/**
 * Class MerchantElectronicContract
 * @package App\Modules\Merchant
 *
 * @property integer merchant_id
 * @property string el_contract_no
 * @property string sign_time
 * @property string expiry_time
 */

class MerchantElectronicContract extends BaseModel
{
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
