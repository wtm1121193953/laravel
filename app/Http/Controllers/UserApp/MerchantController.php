<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Result;

class MerchantController extends Controller
{

    public function getList()
    {

        $data = MerchantService::getListForUserApp([
            'city_id' => request('city_id'),
            'merchant_category_id' => request('merchant_category_id'),
            'keyword' => request('keyword'),
            'lng' => request('lng'),
            'lat' => request('lat'),
            'radius' => request('radius')
        ]);
//        $list = $data['list'];
//        $total = $data['total'];

        return $data;
    }

    /**
     * 获取商户详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);

        $detail = MerchantService::userAppMerchantDetial(
            [
                'id' => request('id'),
                'lng' => request('lng'),
                'lat' => request('lat'),
            ]
        );

        return Result::success(['list' => $detail]);
    }
}