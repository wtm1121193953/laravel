<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 21:01
 */

namespace App\Observers;


use App\DataCacheService;
use App\Modules\Merchant\Merchant;
use App\Support\Lbs;
use Illuminate\Support\Facades\Log;

class MerchantObserver
{

    public function saved(Merchant $merchant)
    {
        Lbs::merchantGpsAdd($merchant->id, $merchant->lng, $merchant->lat);
        DataCacheService::delMerchantDetail([$merchant->id]);
    }
}