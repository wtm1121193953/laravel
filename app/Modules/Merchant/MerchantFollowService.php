<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Oper\Oper;
use Illuminate\Database\Eloquent\Builder;


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

                if($merchantFollowQuery->status == MerchantFollow::USER_NOT_FOLLOW){
                    $merchantFollowQuery->status = MerchantFollow::USER_YES_FOLLOW;
                    $merchantFollowQuery->save();
                }else{
                    throw new BaseResponseException('已关注');
                }

            }else{
                $merchantFollow = new MerchantFollow();
                $merchantFollow->merchant_id = $merchantId;
                $merchantFollow->user_id = $userId;
                $merchantFollow->status = MerchantFollow::USER_YES_FOLLOW;
                $merchantFollow->save();
            }

            $merchant->where('id',$merchantId)->increment('user_follows');
            $follow_status = MerchantFollow::USER_YES_FOLLOW; //返回已关注状态
        }else{

            if($merchantFollowQuery->status == MerchantFollow::USER_NOT_FOLLOW){
                throw new BaseResponseException('已取消');
            }else{
                $merchantFollowQuery->status = MerchantFollow::USER_NOT_FOLLOW;
                $merchantFollowQuery->save();
            }
            $merchant->where('id',$merchantId)->decrement('user_follows');
            $follow_status = MerchantFollow::USER_NOT_FOLLOW; //返回未关注状态
        }
        return $follow_status;
    }

    /**
     * 获取用户关注列表
     * @param $userId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getFollowMerchantList($userId)
    {
        //APP只显示支付到平台的商户
        $query = MerchantFollow::query()
            ->where('user_id',$userId)
            ->where('status',MerchantFollow::USER_YES_FOLLOW)
            ->whereHas('oper', function(Builder $query){
                $query->whereIn('pay_to_platform', [ Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING ]);
            })
            ->paginate();

        return $query;

    }
}