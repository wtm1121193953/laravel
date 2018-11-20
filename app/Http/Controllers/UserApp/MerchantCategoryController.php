<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantCatrgoryService;
use App\Modules\Merchant\MerchantCategoryService;
use App\Result;

class MerchantCategoryController extends Controller
{
    public function getTree()
    {
        $tree = MerchantCategoryService::getTree();
        return Result::success(['list' => $tree]);
    }

    /**
     * 获取超市商户分类
     */
    public function getCsTree(){
        $merchantId = request('merchant_id');
        $list = CsMerchantCatrgoryService::getTree($merchantId);
        return Result::success($list);
    }
}