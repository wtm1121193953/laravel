<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;

class CsMerchantController extends Controller
{

    public function getList()
    {
        $user_key =  request()->get('current_device_no');
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
        ]);

        return Result::success($data);
    }


}