<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/16
 * Time: 18:39
 */

namespace App\Modules\Merchant;


use App\BaseService;

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

    public static function delete()
    {
        // todo 删除分类信息
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
}