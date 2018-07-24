<?php

namespace App\Modules\Order;

use App\BaseModel;

/**
 * Class OrderItem
 * @package App\Modules\Order
 *
 * @property number oper_id
 * @property number merchant_id
 * @property number order_id
 * @property number verify_code
 * @property number status
 */

class OrderItem extends BaseModel
{
    //
    /**
     * 生成核销码
     */
    public static function createVerifyCode($merchantId)
    {
        $verifyCode = rand(100000, 999999);
        if(OrderItem::where('verify_code', $verifyCode)
            ->where('merchant_id', $merchantId)
            ->first()){
            $verifyCode = self::createVerifyCode($merchantId);
        }
        return $verifyCode;
    }
}
