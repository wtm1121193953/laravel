<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Result;

class CsMerchantController extends Controller
{
    public function getList()
    {
        $user_key = request()->get('current_device_no');
        if (empty($user_key)) {
            $user_key = request()->headers->get('token');
            if (empty($user_key)) {
                $user_key = request()->ip();
            }
        }

        $data = CsMerchantService::getListAndDistance([
            'city_id' => request('city_id'),
            'keyword' => request('keyword'),
            'lng' => request('lng'),
            'lat' => request('lat'),
            'radius' => request('radius'),
            'user_key' => $user_key,
            'onlyPayToPlatform' => 1,
            'pageSize' => request('pageSize', 15),
        ]);

        return Result::success($data);

    }

}