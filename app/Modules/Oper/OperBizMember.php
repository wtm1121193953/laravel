<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;


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
     * 发展商户数
     * @param static $operBizMember
     * @param $operId
     * @return int
     */
    public static function getActiveMerchantNumber($operBizMember, $operId)
    {
        $number = Cache::get('oper_biz_member_active_merchant_number_' . $operBizMember->id);
        if (is_null($number)){
            $number = Merchant::where(function (Builder $query) use ($operId){
                $query->where('oper_id', $operId)
                    ->orWhere('audit_oper_id',  $operId);
            })
            ->where('oper_biz_member_code', $operBizMember->code)
            ->where('is_pilot', Merchant::NORMAL_MERCHANT)
            ->count();
            Cache::forever('oper_biz_member_active_merchant_number_' . $operBizMember->id, $number);
            return $number;
        }else{
            return $number ?: 0;
        }
    }

    public static function updateActiveMerchantNumberByCode($code)
    {
        $operBizMember = self::where('code', $code)->first();
        Cache::forget('oper_biz_member_active_merchant_number_' . $operBizMember->id);
    }

    /**
     * 审核通过数
     * @param $operBizMember
     * @param $operId
     * @return int|mixed
     */
    public static function getAuditMerchantNumber($operBizMember, $operId)
    {
        $number = Cache::get('oper_biz_member_audit_merchant_number_' . $operBizMember->id);
        if (is_null($number)){
            $number = Merchant::where(function (Builder $query) use ($operId){
                $query->where('oper_id', $operId)
                    ->orWhere('audit_oper_id',  $operId);
            })
                ->where('audit_status', Merchant::AUDIT_STATUS_SUCCESS)
                ->where('oper_biz_member_code', $operBizMember->code)
                ->where('is_pilot', Merchant::NORMAL_MERCHANT)
                ->count();
            Cache::forever('oper_biz_member_audit_merchant_number_' . $operBizMember->id, $number);
            return $number;
        }else{
            return $number ?: 0;
        }
    }


    /**
     * 更新审核通过数
     * @param $code
     */
    public static function updateAuditMerchantNumberByCode($code)
    {
        $operBizMember = self::where('code', $code)->first();
        Cache::forget('oper_biz_member_audit_merchant_number_' . $operBizMember->id);
    }
}
