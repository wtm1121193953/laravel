<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 18:30
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\DataNotFoundException;
use App\Support\Utils;

class CsPlatformCategoryService extends BaseService
{
    public static function getAll()
    {
        return CsPlatformCategory::select('*')->get();
    }


    /**
     * 获取平台的子分类
     * @param int $parent_id
     * @return array
     */
    public static function getSubCat(int $parent_id=0)
    {
        $rs = CsPlatformCategory::where('parent_id','=',$parent_id)
            ->get();
        $rt = [];
        if ($rs) {
            foreach ($rs as $v) {
                $rt[$v['id']] = $v['cat_name'];
            }
        }
        return $rt;
    }

    /**
     * 获取分类树
     * @param bool $enabled
     * @return array
     */
    public static function getTree($enabled = true)
    {
        if($enabled){
            $list = CsPlatformCategory::where('status', 1)->get();
        }else {
            $list = CsPlatformCategory::all();
        }
        $tree = Utils::convertListToTree($list, 0, null, 'parent_id');
        return $tree;
    }

    /**
     * 获取所有的id名称
     * @param int $useful 获取可用的分类
     * @return array
     */
    public static function getAllIdName($useful = 0)
    {
        if ($useful) {
            $rs = CsPlatformCategory::select('id','cat_name')->where('status',CsPlatformCategory::STATUS_ON)->get();
        } else {
            $rs = CsPlatformCategory::select('id','cat_name')->get();
        }

        $rt = [];
        foreach ($rs as $v) {
            $rt[$v->id] = $v->cat_name;
        }
        return $rt;
    }

    /**
     * 添加平台分类
     * @param $data
     * @return CsPlatformCategory
     */
    public static function add($data)
    {
        $cate = new CsPlatformCategory();
        $cate->cat_name = $data['cat_name'];
        $cate->status = $data['status'];
        $cate->parent_id = $data['parent_id'];
        $cate->level = $cate->parent_id > 0 ? 2 : 1;
        $cate->save();
        return $cate;
    }

    /**
     * 编辑分类信息
     * @param $id
     * @param $data
     * @return CsPlatformCategory
     */
    public static function edit($id, $data)
    {
        $cate = CsPlatformCategory::find($id);
        if(empty($cate)){
            throw new DataNotFoundException('超市分类信息不存在');
        }
        if(isset($data['cat_name'])){
            $cate->cat_name = $data['cat_name'];
        }
        if(isset($data['status'])){
            $cate->status = $data['status'];
        }
        if(isset($data['parent_id'])){
            $cate->parent_id = $data['parent_id'];
        }
        $cate->level = $cate->parent_id > 0 ? 2 : 1;
        $cate->save();
        return $cate;
    }
}