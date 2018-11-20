<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 8:45 PM
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Result;

class CsMerchantController extends Controller
{

    /**
     * 获取商户列表
     */
    public function getLists(){
        $query = CsMerchantService::getAllList();
        return Result::success($query);
    }
}