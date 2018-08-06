<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/24
 * Time: 16:38
 */

namespace App\Modules\Dishes;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\FilterKeyword\FilterKeyword;
use App\Modules\FilterKeyword\FilterKeywordService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DishesCategoryService extends BaseService
{

    /**
     * 获取分类列表
     * @param $merchantId
     * @param $status
     * @param $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getListByMerchantId($merchantId, $status, $pageSize)
    {
        $data = DishesCategory::where('merchant_id', $merchantId)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })->orderBy('sort', 'desc')->paginate($pageSize);

        return $data;
    }

    /**
     * 获取全部的分类列表
     * @param $merchantId
     * @param $status
     * @return DishesCategory[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllList($merchantId, $status)
    {
        $list = DishesCategory::where('merchant_id', $merchantId)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })->orderBy('id', 'desc')->get();
        return $list;
    }

    /**
     * 根据ID以及merchantId获取分类信息
     * @param $id
     * @param $merchantId
     * @return DishesCategory
     */
    public static function getByIdAndMerchantId($id, $merchantId)
    {
        return DishesCategory::where('id', $id)->where('merchant_id', $merchantId)->first();
    }

    /**
     * 添加菜单分类
     * @param $operId
     * @param $merchantId
     * @param $name
     * @param int $status
     * @return DishesCategory
     */
    public static function add($operId, $merchantId, $name, $status = 1)
    {
        // 验证名称是否包含过滤关键字
        FilterKeywordService::filterKeywordByCategory($name, FilterKeyword::CATEGORY_DISHES_CATEGORY_NAME);

        $dishesCategory = new DishesCategory();
        $haveExisitCategory = DishesCategory::where('name',$name)->where('merchant_id', $merchantId)->first();
        if($haveExisitCategory){
            throw new DataNotFoundException('该分类信息已存在，请勿重复添加');
        }

        $dishesCategory->oper_id = $operId;
        $dishesCategory->merchant_id = $merchantId;
        $dishesCategory->name = $name;
        $dishesCategory->status = $status;
        $dishesCategory->sort = DishesCategory::max('sort') + 1;

        $dishesCategory->save();

        return $dishesCategory;
    }

    /**
     * 编辑单品分类信息
     * @param $id
     * @param $merchantId
     * @param $name
     * @param int $status
     * @return DishesCategory
     */
    public static function edit($id, $merchantId, $name, $status = 1)
    {

        $dishesCategory = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($dishesCategory)){
            throw new DataNotFoundException('该分类信息不存在或已删除');
        }

        // 如果名字有变更,验证名称是否包含过滤关键字
        if($dishesCategory->name != $name){
            $haveExisitCategory = DishesCategory::where('name',$name)->where('merchant_id', $merchantId)->first();
            if($haveExisitCategory){
                throw new DataNotFoundException('该分类信息已存在，请改成其他分类名字');
            }
        }
        FilterKeywordService::filterKeywordByCategory(request('name'), FilterKeyword::CATEGORY_DISHES_CATEGORY_NAME);
        $dishesCategory->name = $name;
        $dishesCategory->status = $status;
        $dishesCategory->save();

        return $dishesCategory;
    }

    /**
     * 修改状态
     * @param $id
     * @param $merchantId
     * @param $status
     * @return DishesCategory
     */
    public static function changeStatus($id, $merchantId, $status)
    {
        $dishesCategory = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($dishesCategory)){
            throw new DataNotFoundException('该分类信息不存在或已删除');
        }

        $dishesCategory->status = $status;
        $dishesCategory->save();

        return $dishesCategory;
    }

    /**
     * 删除分类信息
     * @param $id
     * @param $merchantId
     * @return DishesCategory
     * @throws \Exception
     */
    public static function del($id, $merchantId)
    {
        $goodsCount = DishesGoods::where('dishes_category_id', $id)->where('merchant_id', $merchantId)->count();
        if ($goodsCount > 0){
            throw new BaseResponseException('该分类下有' . $goodsCount . '个单品，不能删除！');
        }
        $dishesCategory = DishesCategory::find($id);
        if(empty($dishesCategory)){
            throw new DataNotFoundException('该分类信息不存在或已删除');
        }
        $dishesCategory->delete();
        return $dishesCategory;
    }

    /**
     * 修改排序
     * @param $id
     * @param $merchantId
     * @param string $type
     * @throws \Exception
     */
    public static function changeSort($id, $merchantId, $type = 'up')
    {
        if ($type == 'up'){
            $option = '>';
            $order = 'asc';
        }else{
            $option = '<';
            $order = 'desc';
        }

        $dishesCategory = self::getByIdAndMerchantId($id, $merchantId);
        if (empty($dishesCategory)){
            throw new BaseResponseException('该单品分类不存在');
        }
        $dishesCategoryExchange = DishesCategory::where('merchant_id', $merchantId)
            ->where('sort', $option, $dishesCategory->sort)
            ->orderBy('sort', $order)
            ->first();
        if (empty($dishesCategoryExchange)){
            throw new BaseResponseException('交换位置的单品分类不存在');
        }

        $item = $dishesCategory['sort'];
        $dishesCategory['sort'] = $dishesCategoryExchange->sort;
        $dishesCategoryExchange['sort'] = $item;

        DB::beginTransaction();
        $res1 = $dishesCategory->save();
        $res2 = $dishesCategoryExchange->save();
        if ($res1 && $res2){
            DB::commit();
        }else{
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }
}