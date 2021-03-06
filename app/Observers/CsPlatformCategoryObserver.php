<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/25/025
 * Time: 15:59
 */
namespace App\Observers;

use App\DataCacheService;
use App\Modules\Cs\CsPlatformCategory;
use Illuminate\Support\Facades\Log;

class CsPlatformCategoryObserver
{
    public function saved(CsPlatformCategory $row)
    {
        DataCacheService::delPlatformCats();
        DataCacheService::delPlatformCatsUseful();
    }
}