<?php

namespace App\Modules\Merchant;

use App\BaseModel;
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class MerchantCategory extends BaseModel
{

    //
    public static function getCategoryPath($id)
    {
        $category = MerchantCategory::find($id);
        if($category->pid > 0 && $category->pid != $id){
            $parentPath = self::getCategoryPath($category->pid);
        }else {
            $parentPath = [];
        }
        array_push($parentPath, $category);
        return $parentPath;
    }

}
