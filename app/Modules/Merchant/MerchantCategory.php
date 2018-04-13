<?php

namespace App\Modules\Merchant;

use App\BaseModel;

class MerchantCategory extends BaseModel
{
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
