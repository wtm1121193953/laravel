<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/12/012
 * Time: 15:11
 */

namespace App;

use App\Modules\Cs\CsMerchantCategoryService;
use App\Modules\Cs\CsPlatformCategoryService;
use App\Modules\Merchant\Merchant;
use Illuminate\Support\Facades\Cache;
use test\Mockery\Fixtures\EmptyTestCaseV5;

class DataCacheService extends BaseService
{
    const REDIS_KEY_MERCHANT = 'merchant:id:';//商户信息缓存键值
    const REDIS_KEY_PLATFORM_CATS = 'common_data:platform_cats';//平台分类缓存
    const REDIS_KEY_CS_MERCHANT_CATS = 'common_data:merchant_cats:';//商户分类缓存

    /**
     * 获取商户详情缓存
     * @param $id
     * @return Merchant|mixed
     */
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

    /**
     * 删除商户详情缓存
     * @param array $ids
     */
    public static function delMerchantDetail($ids = [])
    {
        if ($ids) {
            foreach ($ids as $id) {
                $cache_key = self::REDIS_KEY_MERCHANT . $id;
                Cache::store('redis')->forget($cache_key);
            }
        }
    }

    /**
     * 获取平台分类缓存
     * @return array|mixed
     */
    public static function getPlatformCats()
    {
        $data = Cache::store('redis')->get(self::REDIS_KEY_PLATFORM_CATS);
        if (empty($data)) {
            $data = CsPlatformCategoryService::getAllIdName();
            if (!empty($data)) {
                Cache::store('redis')->forever(self::REDIS_KEY_PLATFORM_CATS, json_encode($data));
            }
        } else {
            $data = json_decode($data,true);
        }
        return $data;
    }

    /**
     * 删除平台分类缓存
     */
    public static function delPlatformCats()
    {
        Cache::store('redis')->forget(self::REDIS_KEY_PLATFORM_CATS);
    }

    /**
     * 获取商户分类数据
     * @param $cs_merchant_id
     * @return array|\Illuminate\Support\Collection|mixed|string
     */
    public static function getCsMerchantCats($cs_merchant_id)
    {
        if ($cs_merchant_id <= 0) {
            return '';
        }
        $cache_key = self::REDIS_KEY_CS_MERCHANT_CATS . $cs_merchant_id;
        $data = Cache::store('redis')->get($cache_key);
        if (empty($data)) {
            $data = CsMerchantCategoryService::getTree($cs_merchant_id);
            if (!empty($data)) {
                Cache::store('redis')->forever($cache_key, json_encode($data));
            }
        } else {
            $data = json_decode($data,true);
        }
        return $data;
    }

    /**
     * 删除平台分类缓存
     * @param $cs_merchant_id
     * @return bool|string
     */
    public static function delCsMerchantCats($cs_merchant_id)
    {
        if ($cs_merchant_id <= 0) {
            return '';
        }
        $cache_key = self::REDIS_KEY_CS_MERCHANT_CATS . $cs_merchant_id;
        return Cache::store('redis')->forget($cache_key);
    }

    /**
     * 删除所有商户分类缓存
     * @return bool
     */
    public static function delAllCsMerchantCats()
    {
        $cache_key = self::REDIS_KEY_CS_MERCHANT_CATS . '*';
        return Cache::store('redis')->forget($cache_key);
    }
}