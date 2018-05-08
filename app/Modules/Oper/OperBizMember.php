<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Exceptions\BaseResponseException;

class OperBizMember extends BaseModel
{
    //

    /**
     * 生成业务员推荐码
     * @param int $retry
     * @return string
     */
    public static function genCode($retry = 1000)
    {
        if($retry == 0){
            throw new BaseResponseException('业务员推荐码已超过最大重试次数');
        }
        $code = 'T' . rand(100000, 999999);
        $exists = self::where('code', $code)->first();
        if(!empty($exists)){
            $code = self::genCode(--$retry);
        }
        return $code;
    }

    /**
     * @param static $operBizMember
     * @return int
     */
    public static function getActiveMerchantNumber($operBizMember)
    {
        return 0;
    }
}
