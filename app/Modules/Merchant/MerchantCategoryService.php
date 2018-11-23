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
            })->orderBy('sort')->get();
            $tree = Utils::convertListToTree($list);
            Cache::forever($cacheKey, $tree);
        }
        return $tree;
    }

    /**
     * 新版本获取树形分类信息
     * @param bool $withDisabled
     * @return array|mixed
     */
    public static function getTypeTree($withDisabled = false)
    {
        $cacheKey = $withDisabled ? 'merchant_category_tree_with_disabled' : 'merchant_category_tree';
        $tree = Cache::get($cacheKey);
        $result = Array();
        if(!$tree) {
            $list = MerchantCategory::when(!$withDisabled, function (Builder $query) {
                $query->where('status', 1);
            })->orderBy('sort')->get();
            $tree = Utils::convertListToTree($list);

            if (sizeof($tree) >= 18) {
                $tree = array_slice($tree, 0, 18);
            }
            $merchantCategory = new MerchantCategory();
            $merchantCategory->name = '大千超市';
            $merchantCategory->type = MerchantCategory::TYPE_CS_INDEX;
            $merchantCategory->status = MerchantCategory::STATUS_ON;
            $merchantCategory->icon = 'https://xiaochengxu.niucha.ren/storage/image/item/f1cQ4X4OxXk0o2j1T9bH2wl1B0wr9P5M5VOxTkvq.png';
            array_push($result, $merchantCategory);

            foreach ($tree as $item){
                array_push($result, $item);
            }
            $merchantCategory = new MerchantCategory();
            $merchantCategory->name = '更多';
            $merchantCategory->type = MerchantCategory::TYPE_MERCHANT_CATEGORY_ALL;
            $merchantCategory->status = MerchantCategory::STATUS_ON;
            $merchantCategory->icon = 'https://xiaochengxu.niucha.ren/storage/image/item/f1cQ4X4OxXk0o2j1T9bH2wl1B0wr9P5M5VOxTkvq.png';
            array_push($result, $merchantCategory);

            Cache::forever($cacheKey, $result);
        }
        else{
            return $tree;
        }
        return $result;
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
        if($pid==0){
            $checkPidCategory = MerchantCategory::where('name',$name)->first();
            if($checkPidCategory){
                if($checkPidCategory->pid==0){
                    throw new ParamInvalidException('已存在该顶级类目，请勿重复添加');
                }else{
                    throw new ParamInvalidException('子类目不能成为顶级类目');
                }
            }
        }else{
            $hadExsitPid = MerchantCategory::where('id',$pid)->value('pid');
            $AddCategoryPid = MerchantCategory::where('name',$name)->value('pid');
            if( $hadExsitPid===$AddCategoryPid){
                throw new ParamInvalidException('顶级类目不能作为子类目');
            }
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
     * 清除类目缓存
     */
    public static function clearCache()
    {
        Cache::forget('merchant_category_tree');
        Cache::forget('merchant_category_tree_with_disabled');
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
        $hadExsitPid =$category->pid;

        if($hadExsitPid==0){
            $AddCategoryPid = MerchantCategory::where('name',$name)->value('pid');
            if( $hadExsitPid===$AddCategoryPid &&$category->name!=$name){
                throw new ParamInvalidException('顶级分类不能作为子类目');
            }
        }else{
            $childIdsArray = self::getSubCategoryIds($hadExsitPid)->toArray();
            $childIdsArray = array_diff($childIdsArray,[$id]);
            $AddCategory = MerchantCategory::where('name',$name)->first();
            if($pid==0){
                throw new ParamInvalidException('子类目不能成为顶级类目');
            }else if($AddCategory){
                    if($AddCategory->pid ==0){
                        throw new ParamInvalidException('子类目不能成为顶级类目');
                    }else if(in_array($AddCategory->id,$childIdsArray)){
                         throw new ParamInvalidException('顶级类目下的子类目不能重名');
                  }
            }
        }

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

        if ($category->pid == 0) {
            MerchantCategory::where('pid', $category->id)->update(['status' => $status]);
        }

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

    /**
     * 获取分类的路径(从顶级分类到当前分类)
     * @param $id
     * @param bool $onlyEnabled
     * @return array
     */
    public static function getCategoryPath($id, $onlyEnabled = false)
    {
        $category = MerchantCategory::where('id', $id)
            ->when($onlyEnabled, function (Builder $query) {
                $query->where('status', MerchantCategory::STATUS_ON);
            })
            ->first();
        if($category && $category->pid > 0 && $category->pid != $id){
            $parentPath = self::getCategoryPath($category->pid, $onlyEnabled);
        }else {
            $parentPath = [];
        }
        if ($category) {
            array_push($parentPath, $category);
        }
        return $parentPath;
    }

    /**
     * 获取全部的顶级分类
     * @param bool $enabled
     * @return MerchantCategory[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllTopCategories($enabled = true)
    {
        $list = MerchantCategory::where('pid', 0)
            ->when($enabled, function(Builder $query){
                $query->where('status', 1);
            })
            ->get();
        return $list;
    }

}