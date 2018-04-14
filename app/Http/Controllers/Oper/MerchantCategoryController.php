<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/14
 * Time: 15:53
 */

namespace App\Http\Controllers\Oper;


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