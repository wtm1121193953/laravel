<?php

namespace App\Modules\Oper;

use App\BaseModel;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

use App\Modules\Bizer\BizerService;


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

class MyOperBizer extends BaseModel
{
    /**
     *
     * 根据运营中心获取业务员及商户信息，我的业务员
     * @param $params
     * @param array $fields
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params, $fields = ['*'])
    {
        $operId = array_get($params, 'oper_id');
        $data = OperBizer::when(!empty($operId), function (Builder $query) use ($operId) {
                    $query->where('oper_id', $operId);
                })
                ->where('status', OperBizer::STATUS_SIGNED)
                ->orderBy('id', 'desc')
                ->select($fields)
                ->paginate();

        $data->each(function ($item) {
            $item->divide = $item->divide > 0 ? ($item->divide)."%" : 0;
            $item->bizerInfo = BizerService::getById($item->bizer_id, 'name,mobile,status') ?: null;
            $item->activeNum = MyOperBizer::getActiveMerchantNumber($item->bizer_id,$item->oper_id);
            $item->auditNum = MyOperBizer::getAuditMerchantNumber($item->bizer_id,$item->oper_id);
        });

        return $data;
    }

    /**
     * 发展商户数
     * @param $bizerId
     * @param $operId
     * @return int|mixed
     */
    public static function getActiveMerchantNumber($bizerId, $operId)
    {
        $number = Cache::get('oper_bizer_active_merchant_number_' . $operId . '_with_' . $bizerId);
        if (is_null($number)){
            $number = Merchant::where(function (Builder $query) use ($operId){
                $query->where('oper_id', $operId)
                    ->orWhere('audit_oper_id',  $operId);
            })
            ->where('bizer_id', $bizerId)
            ->where('is_pilot', Merchant::NORMAL_MERCHANT)
            ->count();
            Cache::forever('oper_bizer_active_merchant_number_' . $operId . '_with_' . $bizerId, $number);
            return $number;
        }else{
            return $number ?: 0;
        }
    }

    /**
     * 更新发展商户数量
     * @param $operId
     * @param $bizerId
     */
    public static function updateActiveMerchantNumberByCode($operId, $bizerId)
    {
        Cache::forget('oper_bizer_active_merchant_number_' . $operId . '_with_' . $bizerId);
    }

    /**
     *  审核通过数
     * @param $bizerId
     * @param $operId
     * @return int|mixed
     */
    public static function getAuditMerchantNumber($bizerId, $operId)
    {
        $number = Cache::get('oper_bizer_audit_merchant_number_' . $operId . '_with_' . $bizerId);
        if (is_null($number)){
            $number = Merchant::where(function (Builder $query) use ($operId){
                $query->where('oper_id', $operId)
                    ->orWhere('audit_oper_id',  $operId);
            })
                ->where('audit_status', Merchant::AUDIT_STATUS_SUCCESS)
                ->where('bizer_id', $bizerId)
                ->where('is_pilot', Merchant::NORMAL_MERCHANT)
                ->count();
            Cache::forever('oper_bizer_audit_merchant_number_' . $operId . '_with_' . $bizerId, $number);
            return $number;
        }else{
            return $number ?: 0;
        }
    }


    /**
     * 更新审核通过数
     * @param $operId
     * @param $bizerId
     */
    public static function updateAuditMerchantNumberByCode($operId, $bizerId)
    {
        Cache::forget('oper_bizer_audit_merchant_number_' . $operId . '_with_' . $bizerId);
    }
}
