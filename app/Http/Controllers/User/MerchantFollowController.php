<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantFollowService;
use App\Modules\Merchant\MerchantService;
use App\Result;

class MerchantFollowController extends Controller
{

    public function modifyFollowStatus()
    {
        $user = request()->get('current_user');

        $data = MerchantFollowService::modifyFollows([
            'status' => request('status'), //1-未关注，2-已关注
            'user_id' => $user->id,
            'merchant_id' => request('merchant_id')
        ]);

        return Result::success([
            'data' => $data
        ]);
    }

    public function userFollowList()
    {
        $userID = request()->get('current_user')->id;
        $lng = request('lng',0);
        $lat = request('lat',0);
        //获取用户收藏的商户ID
        $followmerchantList = MerchantFollowService::getFollowMerchantList($userID);

        $follow_merchant_ids = [];
        foreach($followmerchantList as $key){
            array_push($follow_merchant_ids,$key->merchant_id);
        }

        $list = MerchantService::getListByIds($follow_merchant_ids,$lng,$lat);
        $total = $followmerchantList->total();

        return Result::success(['list' => $list, 'total' => $total]);
    }
}