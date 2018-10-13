<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/12/012
 * Time: 15:11
 */

namespace App;

use App\Modules\Merchant\Merchant;
use Illuminate\Support\Facades\Cache;

class DataCacheService extends BaseService
{
    const REDIS_KEY_MERCHANT = 'merchant:id:';

    public static function getMerchantDetail($id)
    {
        $cache_key = self::REDIS_KEY_MERCHANT . $id;
        $data = Cache::store('redis')->get($cache_key);
        if (empty($data)) {
            $data = Merchant::findOrFail($id);
            Cache::store('redis')->forever($cache_key, $data);
        }
        return $data;
    }

    public static function delMerchantDetail($ids = [])
    {
        if ($ids) {
            foreach ($ids as $id) {
                $cache_key = self::REDIS_KEY_MERCHANT . $id;
                Cache::store('redis')->forget($cache_key);
            }
        }
    }
}