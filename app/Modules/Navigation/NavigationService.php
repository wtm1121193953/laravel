<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/11/23
 * Time: 18:03
 */

namespace App\Modules\Navigation;


use App\BaseService;
use App\Exceptions\DataNotFoundException;

class NavigationService extends BaseService
{

    public static function getAll()
    {
        $list = Navigation::orderBy('sort', 'desc')->get();
        $list->each(function($item){
            $item->payload = json_decode($item->payload, 1);
        });
        return $list;
    }

    /**
     * 添加导航数据
     * @param $data
     * @return Navigation
     */
    public static function add($data)
    {
        $nav = new Navigation();
        $nav->title = $data['title'];
        $nav->icon = $data['icon'];
        $nav->type = $data['type'];
        if(!empty($data['payload'])){
            $nav->payload = json_encode($data['payload']);
        }
        $nav->save();
        return $nav;
    }

    /**
     * 编辑分类
     * @param $id
     * @param $data
     * @return Navigation
     */
    public static function edit($id, $data)
    {
        $nav = Navigation::find($id);
        if(empty($nav)){
            throw new DataNotFoundException('导航数据不存在');
        }
        $nav->title = $data['title'];
        $nav->icon = $data['icon'];
        $nav->type = $data['type'];
        if(!empty($data['payload'])){
            $nav->payload = json_encode($data['payload']);
        }
        $nav->save();
        return $nav;
    }

    /**
     * 修改排序
     * @param $id
     * @param $sort
     * @return Navigation
     */
    public static function changeSort($id, $sort)
    {
        $nav = Navigation::find($id);
        if(empty($nav)){
            throw new DataNotFoundException('导航数据不存在');
        }
        $nav->sort = $sort;
        $nav->save();
        return $nav;
    }

    /**
     * 删除导航数据
     * @param $id
     * @return Navigation
     * @throws \Exception
     */
    public static function del($id)
    {
        $nav = Navigation::find($id);
        if(empty($nav)){
            throw new DataNotFoundException('数据已删除或不存在');
        }
        $nav->delete();
        return $nav;
    }

}