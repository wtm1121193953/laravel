<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantCategory;
use App\Result;

class MerchantCategoryController extends Controller
{
    public function getTree()
    {
        $tree = MerchantCategory::getTree();
        return Result::success(['list' => $tree]);
    }
}