<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


/**
 * Class OperBizMember
 * @package App\Modules\Oper
 *
 * @property number oper_id
 * @property string name
 * @property string mobile
 * @property string code
 * @property string remark
 * @property number status
 */

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
        $number = Cache::get('oper_biz_member_active_merchant_number_' . $operBizMember->id);
        if (is_null($number)){
            $number = Merchant::where(function (Builder $query){
                $query->where('oper_id', request()->get('current_user')->oper_id)
                    ->orWhere('audit_oper_id',  request()->get('current_user')->oper_id);
            })
            ->where('oper_biz_member_code', $operBizMember->code)->count();
            Cache::forever('oper_biz_member_active_merchant_number_' . $operBizMember->id, $number);
            return $number;
        }else{
            return $number ?: 0;
        }
    }

    public static function updateActiveMerchantNumberByCode($code)
    {
        $operBizMember = self::where('code', $code)->first();
        if($operBizMember){
            $count = Merchant::where(function (Builder $query){
                $query->where('oper_id', request()->get('current_user')->oper_id)
                    ->orWhere('audit_oper_id',  request()->get('current_user')->oper_id);
            })
            ->where('oper_biz_member_code', $code)->count();
            Cache::forever('oper_biz_member_active_merchant_number_' . $operBizMember->id, $count);
        }else {
            Log::warning('更新运营中心业务人员已激活商家数量缓存时, 所传的code对应的业务人员不存在', [
                'code' => $code,
            ]);
        }
    }
}
