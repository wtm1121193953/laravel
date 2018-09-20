<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\BaseResponseException;


/**
 * 商户关注相关service
 * Class MerchantFollowService
 * @package App\Modules\Merchant
 */
class MerchantFollowService extends BaseService
{
    /**
     * 修改商户关注状态
     * @param $params
     * @return int
     */
    public static function modifyFollows($params)
    {
        $status = array_get($params, "status");
        $userId = array_get($params,'user_id');
        $merchantId = array_get($params,'merchant_id');

        $merchant = new Merchant();
        $merchantFollowQuery = MerchantFollow::where('user_id',$userId)->where('merchant_id',$merchantId)->first();
        if($status ==1){ //未关注，增加记录

            if($merchantFollowQuery){
                throw new BaseResponseException('已关注');
            }else{
                $merchantFollow = new MerchantFollow();
                $merchantFollow->merchant_id = $merchantId;
                $merchantFollow->user_id = $userId;
                $merchantFollow->save();
            }

            $merchant->where('id',$merchantId)->increment('user_follows');
            $follow_status = 2; //返回已关注状态
        }else{
            $merchantFollowQuery->delete();
            $merchant->where('id',$merchantId)->decrement('user_follows');
            $follow_status = 1; //返回未关注状态
        }
        return $follow_status;
    }
}