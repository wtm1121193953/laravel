<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:26
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantCategoryService;
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
     * 获取新的分类列表
     * @author zwg
     */
    public function getTypeTree(){
        $tree = MerchantCategoryService::getTypeTree();
        return Result::success(['list' => $tree]);
    }


    /**
     * 获取超市商户分类
     */
    public function getCsTree(){
        $this->validate(request(),[
            'merchant_id' => 'required|integer|min:1'
        ]);
        $merchantId = request('merchant_id');
        $list = CsMerchantCategoryService::getTree($merchantId);
        return Result::success($list);
    }
}