<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use Qcloud\Cos\Service;

class CsUserAddressService extends Service{
    public static function addAddresses(){
        $user = request()->get('current_user');

    }
}