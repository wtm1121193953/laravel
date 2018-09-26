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
        $data = MerchantService::getListForUserApp([
            'lng' => request('lng'),
            'lat' => request('lat'),
            'page' => request('page'),
        ],true);
        return $data;
    }
}