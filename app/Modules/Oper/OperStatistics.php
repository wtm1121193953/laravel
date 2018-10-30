<?php

namespace App\Modules\Oper;

use App\BaseModel;

/**
 * Class OperStatistics
 * Author:   JerryChan
 * Date:     2018/9/20 17:46
 * @package App\Modules\Oper
 * @property string date
 * @property int oper_id
 * @property int merchant_num
 * @property int merchant_pilot_num
 * @property int merchant_total_num
 * @property int user_num
 * @property int merchant_invite_num
 * @property int oper_and_merchant_invite_num
 * @property float order_paid_num
 * @property float order_refund_num
 * @property float order_paid_amount
 * @property float order_refund_amount
 */
class OperStatistics extends BaseModel
{
    protected $guarded = [];

    //
    /**
     *
     */
    public function oper()
    {
        return $this->belongsTo(Oper::class);
    }
}
