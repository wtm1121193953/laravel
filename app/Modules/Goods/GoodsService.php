<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 19:52
 */

namespace App\Modules\Goods;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Modules\FilterKeyword\FilterKeyword;
use App\Modules\FilterKeyword\FilterKeywordService;
use App\Modules\Merchant\MerchantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GoodsService
{

    /**
     * 获取商品列表
     * @param $merchantId
     * @param null $status
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($merchantId, $status = null)
    {
        $data = Goods::where('merchant_id', $merchantId)
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })->orderBy('sort', 'desc')->paginate();

        return $data;
    }

    /**
     * 首页商户列表，显示价格最低的n个团购商品
     * @param $merchantId
     * @param $number
     * @return Goods[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getLowestPriceGoodsForMerchant($merchantId, $number)
    {
        $list = Goods::where('merchant_id', $merchantId)
            ->where('status', Goods::STATUS_ON)
            ->orderBy('sort', 'desc')
            ->limit($number)
            ->get();
        return $list;
    }

    /**
     * 获取商品详情
     * @param int $id
     * @return Goods|null
     */
    public static function getById($id)
    {
        $goods = Goods::find($id);
        if(empty($goods)){
            return null;
        }
        return $goods;
    }

    /**
     * 根据ID及商户ID获取商品
     * @param $id
     * @param $merchantId
     * @return Goods|null
     */
    public static function getByIdAndMerchantId($id, $merchantId)
    {
        $goods = Goods::where('id', $id)->where('merchant_id', $merchantId)->first();
        if(empty($goods)){
            return null;
        }
        return $goods;
    }

    /**
     * 添加商品
     * @param $operId
     * @param $merchantId
     * @return Goods
     */
    public static function addFromRequest($operId, $merchantId)
    {
        $goods = new Goods();
        $marketPrice = request('market_price', 0);
        $price = request('price', 0);
        if($marketPrice < $price){
            throw new BaseResponseException('市场价不能小于销售价！');
        }
        $endDate = request('end_date');
        self::validateGoodsEndDate($endDate);

        // 检测名称是否包含过滤关键字
        $name = request('name');
        FilterKeywordService::filterKeywordByCategory($name, FilterKeyword::CATEGORY_GOODS_NAME);

        $goods->oper_id = $operId;
        $goods->merchant_id = $merchantId;
        $goods->name = $name;
        $goods->market_price = $marketPrice;
        $goods->price = $price;
        $goods->start_date = request('start_date');
        $goods->end_date = $endDate;
        $goods->pic = request('pic', '');
        $picList = request('pic_list', '');
        if(is_array($picList)){
            $picList = implode(',', $picList);
        }
        $goods->pic_list = $picList;

        $goods->thumb_url = request('thumb_url', '');
        $goods->desc = request('desc', '');
        $goods->buy_info = request('buy_info', '');
        $goods->status = request('status', 1);
        $goods->sort = self::getMaxSort($merchantId) + 1;
        $goods->save();

        // 更新商户最低价格
        MerchantService::updateMerchantLowestAmount($merchantId);

        return $goods;
    }

    /**
     * 编辑商品信息
     * @param $id
     * @param $merchantId
     * @return Goods
     */
    public static function editFromRequest($id, $merchantId)
    {
        $goods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }

        $marketPrice = request('market_price', 0);
        $price = request('price', 0);
        if($marketPrice <= $price){
            throw new BaseResponseException('市场价不能小于销售价！');
        }
        $endDate = request('end_date');
        self::validateGoodsEndDate($endDate);

        // 检测名称是否包含过滤关键字
        $name = request('name');
        FilterKeywordService::filterKeywordByCategory($name, FilterKeyword::CATEGORY_GOODS_NAME);

        $goods->name = $name;
        $goods->market_price = $marketPrice;
        $goods->price = $price;
        $goods->start_date = request('start_date');
        $goods->end_date = $endDate;
        $goods->pic = request('pic', '');
        $picList = request('pic_list', '');
        if(is_array($picList)){
            $picList = implode(',', $picList);
        }
        $goods->pic_list = $picList;
        $goods->thumb_url = request('thumb_url', '');
        $goods->desc = request('desc', '');
        $goods->buy_info = request('buy_info', '');
        $goods->status = request('status', 1);

        $goods->save();

        // 更新商户最低价格
        MerchantService::updateMerchantLowestAmount(request()->get('current_user')->merchant_id);

        return $goods;
    }

    /**
     * 修改商品状态
     * @param $id
     * @param $merchantId
     * @param $status
     * @return Goods
     */
    public static function changeStatus($id, $merchantId, $status)
    {
        $goods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }

        if ($status == Goods::STATUS_ON) {
            self::validateGoodsEndDate($goods->end_date);
        }

        $goods->status = $status;
        $goods->save();

        // 更新商户最低价格
        MerchantService::updateMerchantLowestAmount($merchantId);

        return $goods;
    }

    /**
     * @param $id
     * @param $merchantId
     * @return Goods
     * @throws \Exception
     */
    public static function del($id, $merchantId)
    {

        $goods = self::getByIdAndMerchantId($id, $merchantId);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已被删除');
        }
        $goods->delete();

        MerchantService::updateMerchantLowestAmount($merchantId);
        return $goods;
    }

    public static function changeSort($id, $merchantId, $type = 'up')
    {
        if ($type == 'down'){
            $option = '<';
            $order = 'desc';
        }else{
            $option = '>';
            $order = 'asc';
        }

        $goods = self::getByIdAndMerchantId($id, $merchantId);
        if (empty($goods)){
            throw new BaseResponseException('该团购商品不存在');
        }
        $goodsExchange = $goods::where('merchant_id', $merchantId)
            ->where('sort', $option, $goods['sort'])
            ->orderBy('sort', $order)
            ->first();
        if (empty($goodsExchange)){
            throw new BaseResponseException('交换位置的团购商品不存在');
        }

        $item = $goods['sort'];
        $goods['sort'] = $goodsExchange['sort'];
        $goodsExchange['sort'] = $item;

        try{
            DB::beginTransaction();
            $goods->save();
            $goodsExchange->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }

    /**
     * 初始化新加排序字段sort的数值
     * @author andy
     */
    public static function initSortData()
    {
        Goods::chunk(500, function ($goods) {
            foreach ($goods as $good) {
                $good->sort = $good->id;
                $good->save();
            }
        });
    }

    public static function getMaxSort($merchantId)
    {
        $sort = Goods::where('merchant_id', $merchantId)->max('sort');
        return $sort ?? 0;
    }

    /**
     * 验证商品结束日期是否合法日期
     * @param $endDate
     * @return bool
     */
    private static function validateGoodsEndDate($endDate)
    {
        if ($endDate < date('Y-m-d', time())) {
            throw new ParamInvalidException('商品有效期结束时间不能小于当前时间');
        } else {
            return true;
        }
    }

}