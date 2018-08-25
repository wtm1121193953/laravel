<?php

namespace App\Modules\Settlement;

use App\BaseModel;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use Illuminate\Support\Carbon;

/**
 * Class SettlementPlatform
 * @package App\Modules\Settlement
 *
 * @property int    oper_id
 * @property int    merchant_id
 * @property Carbon date
 * @property number settlement_rate
 * @property number amount
 * @property number charge_amount
 * @property number real_amount
 * @property integer bank_card_type
 * @property string bank_open_name
 * @property string bank_card_no
 * @property string sub_bank_name
 * @property string bank_open_address
 * @property string pay_pic_url
 * @property string invoice_title
 * @property string invoice_no
 * @property int invoice_type
 * @property string invoice_pic_url
 * @property string logistics_name
 * @property string logistics_no
 * @property int status
 * @property int settlement_pay_batch_id
 * @property string pay_batch_no
 */
class SettlementPlatform extends BaseModel
{
    //
    //
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }
}
