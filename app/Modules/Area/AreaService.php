<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 0:40
 */

namespace App\Modules\Area;


use App\Support\Utils;
use function foo\func;
use Illuminate\Database\Eloquent\Builder;
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

    public static function getCityListGroupByFirstLetter()
    {
        $list = Cache::get('cities_group_by_first_letter');
        if(empty($list)){
            $list = Area::where('path',2)->orderBy('first_letter')->get();
            $list = $list->each(function ($item){
                $item->name = str_replace('市', '', $item->name);
            })->groupBy('first_letter')->toArray();
            Cache::forever('cities_group_by_first_letter', $list);
        }
        return $list;
    }

    public static function convertAreaToTree($list, $pid = 0, $tier=3)
    {
        $tree = Utils::convertListToTree($list, $pid, $tier, 'parent_id', 'area_id');
        return $tree;
    }

    /**
     * 搜索地区 2,3级地区
     * @param $name
     * @return Area[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getCityListByKeyword($name)
    {
        $list = Area::where('path', '<>', 1)
            ->where(function (Builder $query) use ($name){
                $query->where('name', 'like', "%$name%")
                    ->orWhere('spell', 'like', "$name%")
                    ->orWhere('letter', 'like', "$name%");
            })->get();

        $list->each(function(Area $item){
            if($item->path == 3){
                $item->parent = Area::where('area_id', $item->parent_id)->first();
            }
        });

        return $list;
    }
}