<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;

class CsUserAddressService extends BaseService{
    public static function addAddresses(){
        $user = request()->get('current_user');

    }
}