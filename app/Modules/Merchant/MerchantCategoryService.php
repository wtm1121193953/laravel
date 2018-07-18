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
use App\Support\Utils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class MerchantCategoryService extends BaseService
{

    /**
     * 查询列表
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList()
    {
        $data = MerchantCategory::paginate();
        return $data;
    }

    /**
     * 获取树形分类信息
     * @param bool $withDisabled
     * @return array|mixed
     */
    public static function getTree($withDisabled = false)
    {
        $cacheKey = $withDisabled ? 'merchant_category_tree_with_disabled' : 'merchant_category_tree';
        $tree = Cache::get($cacheKey);
        if(!$tree){
            $list = MerchantCategory::when(!$withDisabled, function(Builder $query){
                $query->where('status', 1);
            })->get();
            $tree = Utils::convertListToTree($list);
            Cache::forever($cacheKey, $tree);
        }
        return $tree;
    }

    /**
     * 添加分类信息
     * @param $name
     * @param string $icon
     * @param int $status
     * @param int $pid
     * @return MerchantCategory
     */
    public static function add($name, $icon = '', $status = 1, $pid = 0)
    {
        $category = new MerchantCategory();
        $category->name = $name;
        $category->icon = $icon;
        $category->status = $status;
        $category->pid = $pid;
        $category->save();

        self::clearCache();

        return $category;
    }

    /**
     * 编辑分类信息
     * @param $id
     * @param $name
     * @param string $icon
     * @param int $status
     * @param int $pid
     * @return MerchantCategory
     */
    public static function edit($id, $name, $icon = '', $status = 1, $pid = 0)
    {

        $category = MerchantCategory::find($id);
        if(empty($category)){
            throw new ParamInvalidException('类目不存在或已被删除');
        }
        $category->name = $name;
        $category->icon = $icon;
        $category->status = $status;
        $category->pid = $pid;
        $category->save();

        self::clearCache();

        return $category;
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

        self::clearCache();

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

        self::clearCache();

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

    /**
     * 清除类目缓存
     */
    public static function clearCache()
    {
        Cache::forget('merchant_category_tree');
        Cache::forget('merchant_category_tree_with_disabled');
    }

    /**
     * 获取分类子集的id数组
     * @param $categoryId
     * @return bool|\Illuminate\Support\Collection
     */
    public static function getSubCategoryIds($categoryId)
    {
        $subArray = MerchantCategory::where('pid', $categoryId)
            ->select('id')
            ->get()
            ->pluck('id');
        if (count($subArray) > 0){
            return $subArray;
        }else{
            return false;
        }
    }
}