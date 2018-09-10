<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/5
 * Time: 下午3:01
 */

namespace App\Http\Controllers\UserApp;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesGoodsService;
use App\Modules\Dishes\DishesService;
use App\Modules\Merchant\MerchantSettingService;
use App\Result;

class DishesController extends Controller
{
    /**
     * 判断单品购买功能是否开启
     * MerchantDishesController constructor.
     */
    public function __construct()
    {
        $merchantId = request('merchant_id');
        if (!$merchantId) {
            throw new BaseResponseException('商户ID不能为空');
        }
        $result = MerchantSettingService::getValueByKey($merchantId, 'dishes_enabled');
        if (!$result) {
            throw new BaseResponseException('单品购买功能尚未开启！');
        }
    }

    /**
     * 获取单品分类
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDishesCategory()
    {
        //获取分类列表
        $merchantId = request('merchant_id');
        $list = DishesService::getDishesCategory($merchantId);

        foreach ($list as $category) {
            $categoryId = $category['id'];
            if ($categoryId) {
                //获取菜品列表
                $subList = DishesGoodsService::getDishesGoods($merchantId, $categoryId);
                $category['subList'] = $subList;
            } else {
                $category['subList'] = array();
            }

        }
        return Result::success([
            'list' => $list
        ]);
    }


    /**
     * 获取热门菜品
     *
     */
    public function getHotDishesGoods()
    {

        $merchantId = request('merchant_id');
        $hotDishesGoods = DishesGoodsService::getHotDishesGoods($merchantId);
        return Result::success([
            'list' => $hotDishesGoods
        ]);
    }


    /**
     * 获取单品指定分类的商品
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDishesGoods()
    {
        $merchantId = request('merchant_id');
        $categoryId = request('category_id');
        if (!$categoryId) {
            throw new BaseResponseException('分类ID不能为空');
        }

        $list = DishesGoodsService::getDishesGoods($merchantId, $categoryId);

        return Result::success([
            'list' => $list,
        ]);

    }


    //点菜操作

    public function add()
    {
        $userId = request()->get('current_user')->id;
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
        $dishesList = request('goods_list');
        if (is_string($dishesList)) {
            $dishesList = json_decode($dishesList, true);
        }

        $merchantId = request('merchant_id');
        if (empty($dishesList)) {
            throw new ParamInvalidException('单品列表为空');
        }
        if (sizeof($dishesList) < 1) {
            throw new ParamInvalidException('参数不合法1');
        }
        foreach ($dishesList as $item) {
            if (!isset($item['id']) || !isset($item['number'])) {
                throw new ParamInvalidException('参数不合法2');
            }
            $dishesGoods = DishesGoods::findOrFail($item['id']);
            if ($dishesGoods->status == DishesGoods::STATUS_OFF) {
                throw new BaseResponseException('菜单已变更, 请刷新页面');
            }
        }

        $dishes = DishesService::addDishes($merchantId, $userId, $dishesList);

        return Result::success($dishes);
    }

    /**
     * 点菜的菜单详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function detail()
    {
        $this->validate(request(), [
            'dishes_id' => 'required|integer|min:1',
        ]);

        $dishesId = request('dishes_id');
        $detailDishes = DishesService::detailDishes($dishesId);

        return Result::success($detailDishes);
    }
}
