<?php

namespace App\Modules\Cs;

use App\BaseService;

class CsMerchantSettingService extends BaseService
{
    //

    /**
     * 获取商户配送设置，没有则创建一条记录
     * @param $cs_merchant_id
     * @return CsMerchantSetting
     */
    public static function getDeliverSetting($cs_merchant_id)
    {

        $exist = CsMerchantSetting::where('cs_merchant_id',$cs_merchant_id)->first();

        if (empty($exist)) {
            $m = new CsMerchantSetting();
            $m->cs_merchant_id = $cs_merchant_id;
            $m->save();
            $exist = CsMerchantSetting::where('cs_merchant_id',$cs_merchant_id)->first();
        }

        return $exist;
    }
}
