<?php
/**
 * redis并发锁
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/30/030
 * Time: 17:40
 */
namespace App\Support;

use Illuminate\Support\Facades\Redis;

class RedisLock
{
    public static function lock($key, $expire=10){
        $is_lock = Redis::setnx($key, time()+$expire);

        // 不能获取锁
        if(!$is_lock){

            // 判断锁是否过期
            $lock_time = Redis::get($key);

            // 锁已过期，删除锁，重新获取
            if(time()>$lock_time){
                self::unlock($key);
                $is_lock = Redis::setnx($key, time()+$expire);
            }
        }

        return $is_lock? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public static function unlock($key){
        return Redis::del($key);
    }
}