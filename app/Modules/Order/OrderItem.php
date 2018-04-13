<?php

namespace App\Modules\Order;

use App\BaseModel;

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
