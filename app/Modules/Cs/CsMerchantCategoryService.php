<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 9:28 PM
 */

namespace App\Modules\Cs;


use App\BaseService;

class CsMerchantCategoryService extends BaseService
{
    public static function getTree($merchantId){
       $list = CsMerchantCategory::where('cs_category_level',1)
       ->where('cs_merchant_id',$merchantId)
       ->get();
        $list->each(function ($item){
            $item->sub = CsMerchantCategory::where('cs_category_level',2)
                ->where('cs_category_parent_id',$item->id)
                ->get();
        });
        return $list;
    }
}