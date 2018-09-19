<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 16:46
 */

namespace App\Http\Controllers\UserApp;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\DishesCategory;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Merchant\MerchantSettingService;
use App\Result;

class MerchantDishesController extends Controller
{
    /**
     * 判断单品（单个）购买功能是否开启
     * MerchantDishesController constructor.
     */
    public function __construct()
    {
        $merchantId = request('merchant_id');
        $key = request('key');
        if (!$merchantId || !$key){
            throw new BaseResponseException('商户ID和商户单品购买设置的键不能为空');
        }
        $result = MerchantSettingService::getValueByKey($merchantId, $key);
        if (!$result){
            throw new BaseResponseException('单品购买功能尚未开启！');
        }
    }


    /**
     * 获取菜的分类
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDishesCategory()
    {
        $merchantId = request('merchant_id');
        $list = DishesCategory::where('merchant_id', $merchantId)
            ->where('status', 1)
            ->orderBy('sort')
            ->get();
        return Result::success([
            'list' => $list
        ]);
    }

    /**
     * 获取单个分类菜的下面的菜品
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDishesGoods()
    {
        $merchantId = request('merchant_id');
        $categoryId = request('dashes_category_id');
        if (!$categoryId){
            throw new BaseResponseException('分类ID不能为空');
        }
        $list = DishesGoods::where('merchant_id', $merchantId)
            ->where('status', 1)
            ->where('dishes_category_id',$categoryId)
            ->get();
        return Result::success([
            'list' => $list,
        ]);
    }



}