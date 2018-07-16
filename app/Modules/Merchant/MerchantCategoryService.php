<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 18:39
 */

namespace App\Modules\Merchant;


use App\BaseService;
use App\Exceptions\ParamInvalidException;

class MerchantCategoryService extends BaseService
{

    public static function getList()
    {
        // todo 查询列表
    }

    public static function getAll()
    {
        // todo 查询全部列表
    }

    public static function getTree()
    {
        // todo 获取树形分类信息
    }

    public static function add()
    {
        // todo 添加分类信息
    }

    public static function edit()
    {
        // todo 编辑分类信息
    }

    /**
     * 更新类目状态
     * @param $id
     * @param $status
     * @return MerchantCategory
     */
    public static function changeStatus($id, $status)
    {
        $category = MerchantCategory::find($id);
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }
        $category->status = $status;
        $category->save();

        MerchantCategory::clearCache();

        return $category;
    }

    /**
     * 删除分类信息
     * @param $id
     * @return MerchantCategory
     * @throws \Exception
     */
    public static function delete($id)
    {
        $category = MerchantCategory::find($id);
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }

        // 判断该类别下是否有子类别
        if( self::hasSubCategory($category->id) ){
            throw new ParamInvalidException('该类目下存在子类目, 请先删除子类目再删除该类目');
        }
        if( self::hasMerchant($category->id) ){
            throw new ParamInvalidException('该类目下存在商家, 请先修改商家所属类目信息');
        }

        $category->delete();

        MerchantCategory::clearCache();

        return $category;
    }

    /**
     * 返回商品分类下是否有商家
     * @param $categoryId
     * @return bool
     */
    public static function hasMerchant($categoryId) : bool
    {
        return !empty(Merchant::where('merchant_category_id', $categoryId)->first());
    }

    /**
     * 返回商户分类下是否有子分类
     * @param $categoryId
     * @return bool
     */
    public static function hasSubCategory($categoryId) : bool
    {
        return !empty( MerchantCategory::where('pid', $categoryId)->first() );
    }
}