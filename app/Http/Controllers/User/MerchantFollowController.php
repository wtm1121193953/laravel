<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantFollowService;
use App\Modules\Merchant\MerchantService;
use App\Result;

class MerchantFollowController extends Controller
{

    public function modifyFollowStatus()
    {
        $user = request()->get('current_user');
        $merchantId = request('merchant_id');
        $status = request('status');

        if (!$merchantId) {
            throw new BaseResponseException('商户ID不能为空');
        }

        if (!$status) {
            throw new BaseResponseException('状态不能为空');
        }

        $data = MerchantFollowService::modifyFollows([
            'status' => $status, //1-未关注，2-已关注
            'user_id' => $user->id,
            'merchant_id' => $merchantId,
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
        $data = MerchantFollowService::getFollowMerchantList($userID,$lng,$lat,false);

        return Result::success($data);
    }
}