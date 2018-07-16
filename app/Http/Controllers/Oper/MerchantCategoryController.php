<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/14
 * Time: 15:53
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantCategoryService;
use App\Result;

class MerchantCategoryController extends Controller
{
    public function getTree()
    {
        $tree = MerchantCategoryService::getTree();
        return Result::success(['list' => $tree]);
    }
}