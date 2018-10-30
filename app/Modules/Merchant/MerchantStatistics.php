<?php

namespace App\Modules\Merchant;

use App\BaseModel;

/**
 * Class MerchantStatistics
 * @package App\Modules\Merchant
 * @property string date
 * @property integer merchant_id
 * @property integer oper_id
 * @property integer invite_user_num
 * @property integer order_finished_num
 * @property float order_finished_amount
 */

class MerchantStatistics extends BaseModel
{
    protected $guarded = [];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
