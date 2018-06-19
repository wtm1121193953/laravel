<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class MerchantCategory extends BaseModel
{

    public static function getTree($withDisabled = false)
    {
        $cacheKey = $withDisabled ? 'merchant_category_tree_with_disabled' : 'merchant_category_tree';
        $tree = Cache::get($cacheKey);
        if(!$tree){
            $list = MerchantCategory::when(!$withDisabled, function(Builder $query){
                $query->where('status', 1);
            })->get();
            $tree = Utils::convertListToTree($list);
            Cache::forever($cacheKey, $tree);
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

    public static function clearCache()
    {
        Cache::forget('merchant_category_tree');
        Cache::forget('merchant_category_tree_with_disabled');
    }
}
