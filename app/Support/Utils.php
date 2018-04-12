<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 14:31
 */

namespace App\Support;


class Utils
{

    public static function convertListToTree($list, $pid=0, $tier = null, $pidKey = 'pid', $idKey = 'id')
    {
        if(!is_null($tier)) $tier--;
        $tree = [];
        foreach ($list as &$item) {
            if($item->{$pidKey} == $pid){
                if(is_null($tier) || $tier > 0){
                    $sub = self::convertListToTree($list, $item->{$idKey}, $tier, $pidKey, $idKey);
                    $item->sub = $sub;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}