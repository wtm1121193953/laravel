<?php

namespace App\Modules\CsOrder;

use App\BaseService;
use App\Modules\Cs\CsGood;

class CsOrderGoodService extends BaseService
{


    /**
     * 订单取消增加库存 减销量
     * @param $order_id
     * @return bool
     */
    public static function orderCancel($order_id)
    {

        $list = CsOrderGood::where('order_id',$order_id)->get();
        if (empty($list)) {
            return false;
        }

        foreach ($list as $item) {
            $good = CsGood::findOrFail($item->cs_goods_id);
            $good->sale_num -= $item->number;
            $good->stock += $item->number;
            $good->save();
        }

        return true;
    }
}
