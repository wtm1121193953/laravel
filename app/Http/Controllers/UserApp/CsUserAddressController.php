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
class CsUserAddressController extends Controller{
public function addUserAddresses(){
    $data = ['bank_card_open_name' => request('bank_card_open_name'), 'bank_card_no' => request('bank_card_no'), 'bank_name' => request('bank_name')];
    CsUserAddressService::addAddresses($data);
    return Result::success('添加收货地址成功');
}
}