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
use App\Result;
use App\Support\Utils;
use Illuminate\Support\Facades\Cache;

class MerchantCategoryController extends Controller
{
    public function getTree()
    {
        $tree = Cache::get('merchant_category_tree');
        if(!$tree){
            $list = MerchantCategory::where('status', 1)->get();
            $tree = Utils::convertListToTree($list);
            Cache::forever('merchant_category_tree', $tree);
        }
        return Result::success(['list' => $tree]);
    }
}