<?php

namespace App\Modules\User;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Merchant\Merchant;

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

    /**
     * Author:   JerryChan
     * Date:     2018/9/19 18:02
     * @param $userId
     * @param $merchantId
     */
    public static function addCollect( $userId, $merchantId )
    {
        // 验证对应的商户信息是否存在
        $merchantExists = Merchant::where('id', $merchantId)
            ->where('status', Merchant::STATUS_ON)
            ->exists();
        if (!$merchantExists) {
            throw new BaseResponseException('商铺信息不存在，不可关注');
        }
        // 验证用户是否重复关注
        $exists = self::getCollectionByUserAndMerchantExists($userId, $merchantId);
        if ($exists) {
            throw new BaseResponseException('改用户已关注该商铺，不可重复关注');
        }
        // 新增
        $userCollectMerchant = new UserCollectMerchant();
        $userCollectMerchant->user_id = $userId;
        $userCollectMerchant->merchant_id = $merchantId;
        $userCollectMerchant->save();
    }

    /**
     * 删除收藏
     * Author:   JerryChan
     * Date:     2018/9/19 18:21
     * @param $userId
     * @param $merchantId
     * @throws \Exception
     */
    public static function delCollect( $userId, $merchantId )
    {
        // 验证用户是否重复关注
        $collect = self::getCollectByUserAndMerchant($userId, $merchantId);
        if (!$collect) {
            throw new BaseResponseException('并无用户收藏信息');
        }
        // 删除失败
        if(!$collect->delete()){
            throw new BaseResponseException('删除失败');
        }
    }

    /**
     * 获取列表
     * Author:   JerryChan
     * Date:     2018/9/19 18:29
     * @param $userId
     * @return UserCollectMerchant[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getListByUserId( $userId )
    {
        return UserCollectMerchant::where('user_id', $userId)->get();
    }
}
