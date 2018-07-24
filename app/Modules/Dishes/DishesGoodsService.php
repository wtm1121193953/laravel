<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/24
 * Time: 17:25
 */

namespace App\Modules\Dishes;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\FilterKeyword\FilterKeyword;
use App\Modules\FilterKeyword\FilterKeywordService;
use App\Modules\Goods\Goods;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DishesGoodsService extends BaseService
{

    public static function getById($id)
    {
        return DishesGoods::find($id);
    }

    public static function getByIdAndMerchantId($id, $merchantId)
    {
        return DishesGoods::where('id', $id)->where('merchant_id', $merchantId)->first();
    }

    /**
     * 获取最大的排序值, 传categoryId时获取当前分类下的最大排序值
     * @param $merchantId
     * @param int $categoryId
     * @return int|number
     */
    public static function getMaxSort($merchantId, $categoryId = null)
    {
        $sort = DishesGoods::where('merchant_id', $merchantId)
            ->when(!empty($categoryId), function (Builder $query) use ($categoryId){
                $query->where('dishes_category_id', $categoryId);
            })->max('sort');
        return $sort ?? 0;
    }

    /**
     * 获取单品商品列表
     * @param $params array 查询参数 {merchantId, status, name, categoryId}
     * @param $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params, $pageSize)
    {
        $merchantId = array_get($params, 'merchantId');
        $status = array_get($params, 'status');
        $name = array_get($params, 'name');
        $categoryId = array_get($params, 'categoryId');

        $data = DishesGoods::when($merchantId, function (Builder $query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->when($name, function (Builder $query) use ($name) {
                $query->where('name', 'like', "%$name%");
            })
            ->when($categoryId, function (Builder $query) use ($categoryId) {
                $query->where('dishes_category_id', $categoryId);
            })
            ->orderBy('sort', 'desc')
            ->with('dishesCategory:id,name')
            ->paginate($pageSize);

        return $data;
    }

    /**
     * 添加单品商品
     * @param $operId
     * @param $merchantId
     * @return DishesGoods
     */
    public static function addFromRequest($operId, $merchantId)
    {
        $name = request('name');
        $dishesGoodsList = DishesGoods::where('merchant_id', $merchantId)
            ->where('name', $name)
            ->first();
        if (!empty($dishesGoodsList)) {
            throw new BaseResponseException('商品名称重复！');
        }
        $marketPrice = request('market_price', 0);
        $salePrice = request('sale_price', 0);
        if ($marketPrice < $salePrice) {
            throw new BaseResponseException('市场价必须大于销售价！');
        }
        // 检测商品名中是否存在过滤关键字
        FilterKeywordService::filterKeywordByCategory($name, FilterKeyword::CATEGORY_DISHES_GOODS_NAME);

        $categoryId = request('dishes_category_id', 0);
        $dishesGoods = new DishesGoods();
        $dishesGoods->oper_id = $operId;
        $dishesGoods->merchant_id = $merchantId;
        $dishesGoods->name = $name;
        $dishesGoods->market_price = $marketPrice;
        $dishesGoods->sale_price = $salePrice;
        $dishesGoods->dishes_category_id = $categoryId;
        $dishesGoods->intro = request('intro', '');
        $dishesGoods->detail_image = request('detail_image', '');
        $dishesGoods->status = request('status', 1);
        $dishesGoods->is_hot = request('is_hot', 0);
        $dishesGoods->sort = self::getMaxSort($merchantId, $categoryId) + 1;

        $dishesGoods->save();

        // 更新商家最低消费价
        Goods::updateMerchantLowestAmount($merchantId);

        return $dishesGoods;
    }

    /**
     * 编辑商品信息
     * @param $id
     * @param $merchantId
     * @return DishesGoods
     */
    public static function editFromRequest($id, $merchantId)
    {
        $name = request('name');
        $dishesGoods = DishesGoods::where('merchant_id', $merchantId)
            ->where('name', $name)
            ->where('id', '<>', $id)
            ->first();
        if (!empty($dishesGoods)){
            throw new BaseResponseException('商品名称重复！');
        }

        $dishesGoods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($dishesGoods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }
        $marketPrice = request('market_price', 0);
        $salePrice = request('sale_price', 0);
        if($marketPrice < $salePrice){
            throw new BaseResponseException('市场价必须大于销售价！');
        }
        // 检测商品名中是否存在过滤关键字
        FilterKeywordService::filterKeywordByCategory($name, FilterKeyword::CATEGORY_DISHES_GOODS_NAME);

        $dishesGoods->name = $name;
        $dishesGoods->market_price = $marketPrice;
        $dishesGoods->sale_price = $salePrice;
        $dishesGoods->dishes_category_id = request('dishes_category_id', 0);
        $dishesGoods->intro = request('intro', '');
        $dishesGoods->detail_image = request('detail_image', '');
        $dishesGoods->status = request('status', 1);
        $dishesGoods->is_hot = request('is_hot', 0);

        $dishesGoods->save();

        // 更新商家最低消费价
        Goods::updateMerchantLowestAmount(request()->get('current_user')->merchant_id);

        return $dishesGoods;
    }

    /**
     * 修改商品状态
     * @param $id
     * @param $merchantId
     * @param $status
     * @return DishesGoods
     */
    public static function changeStatus($id, $merchantId, $status)
    {
        $dishesGoods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($dishesGoods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }
        $dishesGoods->status = $status;
        $dishesGoods->save();

        return $dishesGoods;
    }

    /**
     * 删除单品商品
     * @param $id
     * @param $merchantId
     * @return DishesGoods
     * @throws \Exception
     */
    public static function del($id, $merchantId)
    {
        $dishesGoods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($dishesGoods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }
        $dishesGoods->delete();
        Goods::updateMerchantLowestAmount($merchantId);
        return $dishesGoods;
    }

    public static function changeSort($id, $merchantId, $categoryId = null, $type = 'up')
    {
        if ($type == 'down'){
            $option = '<';
            $order = 'desc';
        }else{
            $option = '>';
            $order = 'asc';
        }

        $dishesGoods = self::getByIdAndMerchantId($id, $merchantId);
        if (empty($dishesGoods)){
            throw new BaseResponseException('商品信息不存在或已被删除');
        }
        $dishesGoodsExchange = DishesGoods::where('merchant_id', $merchantId)
            ->where('sort', $option, $dishesGoods['sort'])
            ->when($categoryId, function (Builder $query) use ($categoryId) {
                $query->where('dishes_category_id', $categoryId);
            })
            ->orderBy('sort', $order)
            ->first();
        if (empty($dishesGoodsExchange)){
            throw new BaseResponseException('交换位置的单品不存在');
        }

        $item = $dishesGoods['sort'];
        $dishesGoods['sort'] = $dishesGoodsExchange['sort'];
        $dishesGoodsExchange['sort'] = $item;

        try{
            DB::beginTransaction();
            $dishesGoods->save();
            $dishesGoodsExchange->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }

}