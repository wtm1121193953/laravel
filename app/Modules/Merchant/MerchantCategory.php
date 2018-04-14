<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Support\Utils;
use Illuminate\Support\Facades\Cache;

class MerchantCategory extends BaseModel
{

    public static function getTree()
    {
        $tree = Cache::get('merchant_category_tree');
        if(!$tree){
            $list = MerchantCategory::where('status', 1)->get();
            $tree = Utils::convertListToTree($list);
            Cache::forever('merchant_category_tree', $tree);
        }
        return $tree;
    }
    //
    public static function getCategoryPath($id)
    {
        $category = MerchantCategory::find($id);
        if($category->pid > 0){
            $parentPath = self::getCategoryPath($category->pid);
        }else {
            $parentPath = [];
        }
        array_unshift($parentPath, $category);
        return $parentPath;
    }
}
