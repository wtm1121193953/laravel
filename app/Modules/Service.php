<?php

namespace App\Modules;
use Illuminate\Support\Facades\Cache;


/**
 * 基础Service 提供给具体的service类继承
 * Class Service
 * @package App\Modules
 */
class Service
{

    /**
     * 数据缓存时间, 暂定3天
     */
    protected static $cacheTime = 60 * 24 * 3;

    /**
     * 数据缓存前缀
     */
    private static $cachePrefix = 'data:';

    protected static function setCache($key, $value)
    {
        Cache::put(self::$cachePrefix . $key, $value, self::$cacheTime);
    }

    protected static function getCache($key){
        return Cache::get(self::$cachePrefix . $key);
    }

    protected static function removeCache($key){
        Cache::forget(self::$cachePrefix . $key);
    }


}