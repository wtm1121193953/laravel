<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 15:54
 */

namespace App\Http\Controllers\Admin;


use App\Modules\Area\Area;
use App\Result;
use Illuminate\Support\Facades\Cache;

class AreaController
{
    /**
     * 获取树形地区表
     */
    public function getTree()
    {
        $tier = request('tier', 3);
        $tree = Cache::get('area_tree_tier' . $tier);
        if(empty($tree)){
            $list = Area::all();
            $tree = $this->convertAreaToTree($list, 0, $tier);
            Cache::forever('area_tree_tier' . $tier, $tree);
        }
        return Result::success(['list' => $tree]);
    }

    private function convertAreaToTree($list, $pid = 0, $tier=3)
    {
        $tier --;
        $tree = [];
        foreach ($list as &$item) {
            if($item->parent_id == $pid){
                if($tier > 0){
                    $sub = $this->convertAreaToTree($list, $item->area_id, $tier);
                    $item->sub = $sub;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}