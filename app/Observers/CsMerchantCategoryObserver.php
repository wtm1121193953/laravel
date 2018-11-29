<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/25/025
 * Time: 15:59
 */
namespace App\Observers;

use App\DataCacheService;
use App\Modules\Cs\CsMerchantCategory;
use Illuminate\Support\Facades\Log;

class CsMerchantCategoryObserver
{
    public function saved(CsMerchantCategory $cs_merchant_category)
    {
        DataCacheService::delCsMerchantCats($cs_merchant_category->cs_merchant_id);
    }
}