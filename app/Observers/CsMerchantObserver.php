<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 21:01
 */

namespace App\Observers;


use App\DataCacheService;
use App\Modules\Cs\CsMerchant;
use App\Support\Lbs;
use Illuminate\Support\Facades\Log;

class CsMerchantObserver
{

    public function saved(CsMerchant $csMerchant)
    {
        Log::info('触发观察者 cs_merchant saved');
        Lbs::csMerchantGpsAdd($csMerchant->id, $csMerchant->lng, $csMerchant->lat);
        DataCacheService::delCsMerchantDetail([$csMerchant->id]);
    }
}