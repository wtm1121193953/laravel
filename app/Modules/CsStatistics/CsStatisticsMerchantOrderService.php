<?php

namespace App\Modules\CsStatistics;

use App\BaseService;
use App\Exceptions\BaseResponseException;

class CsStatisticsMerchantOrderService extends BaseService
{
    //

    /**
     * 商户当日销量+1
     * @param $cs_merchant_id
     * @return int
     */
    public static function addMerchantOrderNumberToday($cs_merchant_id)
    {

        $obj = self::createMerchantData($cs_merchant_id);
        if (empty($obj->id)) {
            throw new BaseResponseException('创建初始数据失败');
        }
        return $obj->increment('order_number_today');
    }

    /**
     * 商户当日销量-1
     * @param $cs_merchant_id
     * @return int
     */
    public static function minusCsMerchantOrderNumberToday($cs_merchant_id)
    {
        $obj = self::createMerchantData($cs_merchant_id);
        if (empty($obj->id)) {
            throw new BaseResponseException('创建初始数据失败');
        }
        //如果为0就不减了，不能为负数
        if ($obj->order_number_today == 0) {
            return $obj;
        }
        return $obj->decrement('order_number_today');
    }

    /**
     * 创建商户统计初始数据
     * @param $cs_merchant_id
     * @return CsStatisticsMerchantOrder|bool
     */
    public static function createMerchantData($cs_merchant_id)
    {

        if ($cs_merchant_id<=0) {
            return false;
        }

        $obj = CsStatisticsMerchantOrder::where('cs_merchant_id', $cs_merchant_id)->first();
        if (empty($obj)) {

            $obj = new CsStatisticsMerchantOrder();
            $obj->cs_merchant_id = $cs_merchant_id;
            $obj->order_number_30d = 0;
            $obj->order_number_today = 0;
            $obj->save();
        }
        return $obj;


    }
}
