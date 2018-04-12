<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantCategory;
use App\Support\Utils;

class MerchantCategoryController extends Controller
{
    public function getList()
    {
        $list = MerchantCategory::where('status', 1)->get();
        $tree = Utils::convertListToTree($list);
        return $tree;
    }
}