<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/12/012
 * Time: 15:11
 */

namespace App;

use App\Modules\Merchant\Merchant;
use Illuminate\Support\Facades\Redis;
use test\Mockery\Fixtures\EmptyTestCaseV5;

class DataCacheService extends BaseService
{
    const REDIS_KEY_MERCHANT = 'cache:merchant:';

    public static function getMerchantDetail($id)
    {
        $cache_key = self::REDIS_KEY_MERCHANT . $id;
        $data = Redis::get($cache_key);
        if (empty($data)) {
            $data = Merchant::findOrFail($id);
            $data = serialize($data);
            Redis::set($cache_key, $data);
        }
        $data = @unserialize($data);
        return $data;
    }

    public static function delMerchantDetail($ids = [])
    {
        if ($ids) {
            foreach ($ids as $id) {
                $cache_key = self::REDIS_KEY_MERCHANT . $id;
                Redis::del($cache_key);
            }
        }
    }
}