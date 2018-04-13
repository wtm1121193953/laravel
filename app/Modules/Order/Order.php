<?php

namespace App\Modules\Order;

use App\BaseModel;
use App\Exceptions\BaseResponseException;

class Order extends BaseModel
{
    //
    const STATUS_UN_PAY = 1;
    const STATUS_CANCEL = 2;
    const STATUS_CLOSED = 3;
    const STATUS_PAID = 4;
    const STATUS_REFUNDING = 5;
    const STATUS_REFUNDED = 6;
    const STATUS_FINISHED = 7;

    /**
     * 生成订单号, 订单号规则: O{年月日时分秒}{6位随机数}
     * @param int $retry
     * @return string
     */
    public static function genOrderNo($retry = 100)
    {
        if($retry == 0){
            throw new BaseResponseException('订单号生成已超过最大重试次数');
        }
        $orderNo = 'O' . date('YmdHis') . rand(100000, 999999);
        if(Order::where('order_no', $orderNo)->first()){
            $orderNo = self::genOrderNo(--$retry);
        }
        return $orderNo;
    }

}
