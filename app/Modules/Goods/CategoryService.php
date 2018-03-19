<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 20:46
 */

namespace App\Modules\Goods;


class CategoryService
{

    /**
     * 根据分类信息获取分类路径
     * @param int|Category $category
     * @return array<Category>
     */
    public static function getCatePath($category)
    {
        if(is_int($category)){
            $category = Category::findOrFail($category);
        }
        if($category->pid == 0){
            $path = [$category];
        }else {
            $path = self::getCatePath($category->pid);
            $path[] = $category;
        }
        return $path;
    }
}