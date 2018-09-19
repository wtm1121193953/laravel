<?php

namespace App\Modules\User;

use App\BaseService;
use App\Modules\User\UserCollectMerchant;

/**
 * 用户收藏店铺service
 * Class UserCollectMerchantService
 * Author:   JerryChan
 * Date:     2018/9/19 12:12
 * @package App\Modules\User
 */
class UserCollectMerchantService extends BaseService
{

    /**
     * 通过用户ID与商铺ID获取数据
     * Author:   JerryChan
     * Date:     2018/9/19 12:29
     * @param $userId
     * @param $merchantId
     * @return \App\Modules\User\UserCollectMerchant
     */
    public static function getCollectByUserAndMerchant( $userId, $merchantId )
    {
        return UserCollectMerchant::where('user_id', $userId)
            ->where('merchant_id', $merchantId)
            ->first();
    }

    /**
     * 通过用户ID与商铺ID判断是否收藏
     * Author:   JerryChan
     * Date:     2018/9/19 12:29
     * @param $userId
     * @param $merchantId
     * @return bool
     */
    public static function getCollectionByUserAndMerchantExists( $userId, $merchantId )
    {
        return UserCollectMerchant::where('user_id', $userId)
            ->where('merchant_id', $merchantId)
            ->exists();
    }
}
