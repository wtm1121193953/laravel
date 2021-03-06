<?php

namespace App\Modules\CsOrder;

use App\BaseModel;
use App\Modules\Cs\CsGood;

/**
 * 购买超市商品
 * Class CsOrderGood
 * @package App\Modules\CsOrder
 * @property integer  id
 * @property integer  oper_id
 * @property integer cs_merchant_id
 * @property integer order_id
 * @property integer cs_goods_id
 * @property float price
 * @property integer number
 * @property string goods_name
 */

class CsOrderGood extends BaseModel
{
    //
    public function cs_goods()
    {
        return $this->belongsTo(CsGood::class);
    }
}
