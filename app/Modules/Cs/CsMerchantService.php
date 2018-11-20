<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 11:29 AM
 */
namespace App\Modules\Cs;

use App\BaseService;

class CsMerchantService extends BaseService {

    public static function getById($id, $fields = ['*'])
    {
        return CsMerchant::find($id, $fields);
    }
}