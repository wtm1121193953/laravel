<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        return Cache::get('oper_biz_member_active_merchant_number_' . $operBizMember->id) ?: 0;
    }

    public static function updateActiveMerchantNumberByCode($code)
    {
        $operBizMember = self::where('code', $code)->first();
        if($operBizMember){
            $count = Merchant::where('oper_biz_member_code', $code)->count();
            Cache::forever('oper_biz_member_active_merchant_number_' . $operBizMember->id, $count);
        }else {
            Log::warning('更新运营中心业务人员已激活商家数量缓存时, 所传的code对应的业务人员不存在', [
                'code' => $code,
            ]);
        }
    }
}
