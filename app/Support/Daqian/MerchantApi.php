<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/9/28
 * Time: 19:24
 */

namespace App\Support\Daqian;

/**
 * Class MerchantApi
 * @package App\Support\Daqian
 */
class MerchantApi extends DaqianApi
{
    protected static $appKey;
    protected static $host;

    protected static $getMerchantsUri = '';

    /**
     * 初始化配置方法
     */
    public static function init()
    {
        static::$appKey = config('daqian_api.merchant.app_key');
        static::$host = config('daqian_api.merchant.host');
    }

    /**
     * 获取商户列表
     * @param array $params
     * @return mixed
     * @throws ApiRequestException
     */
    public static function getMerchants(array $params)
    {
        self::init();
        $result = parent::get(static::$appKey, static::$host . self::$getMerchantsUri, $params);
        parent::defaultHandleResult($result);
        return $result;
    }

}