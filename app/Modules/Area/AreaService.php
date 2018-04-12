<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 0:40
 */

namespace App\Modules\Area;


use Illuminate\Support\Facades\Cache;

class AreaService
{

    public static function getAsTree($tier = 3)
    {
        $tree = Cache::get('area_tree_tier' . $tier);
        if(empty($tree)){
            $list = Area::all();
            $tree = self::convertAreaToTree($list, 0, $tier);
            Cache::forever('area_tree_tier' . $tier, $tree);
        }
        return $tree;
    }


    public static function convertAreaToTree($list, $pid = 0, $tier=3)
    {
        $tier --;
        $tree = [];
        foreach ($list as &$item) {
            if($item->parent_id == $pid){
                if($tier > 0){
                    $sub = self::convertAreaToTree($list, $item->area_id, $tier);
                    $item->sub = $sub;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}