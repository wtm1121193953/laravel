<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 18:30
 */
namespace App\Modules\Cs;

use App\BaseService;

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
}