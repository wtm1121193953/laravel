<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 10:47 AM
 */

namespace App\HTTP\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Modules\Cs\CsUserAddressService;
use App\Result;

class CsUserAddressController extends Controller{
public function addUserAddresses(){
    $default = 0;
    if (!empty(request('is_default'))){
        $default = request('is_default');
    }
    $data = ['contacts' => request('contacts'),
                'contact_phone' => request('contact_phone'),
                'province_id' => request('province_id'),
                'city_id' => request('city_id'),
                'area_id' => request('area_id'),
                'address' => request('address'),
                'is_default' => $default];
    CsUserAddressService::addAddresses($data);
    return Result::success('添加收货地址成功');
}
}