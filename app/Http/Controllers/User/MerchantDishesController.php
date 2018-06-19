<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 16:46
 */

namespace App\Http\Controllers\User;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Dishes\Dishes;
use App\Modules\Dishes\DishesCategory;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantSettingService;
use App\Result;

class MerchantDishesController extends Controller
{
    /**
     * 判断单品购买功能是否开启
     * MerchantDishesController constructor.
     */
    public function __construct()
    {
        $merchantId = request('merchant_id');
        //        $key = request('key');
        $key = "dishes_enabled";
        if (!$merchantId || !$key){
            throw new BaseResponseException('商户ID和商户单品购买设置的键不能为空');
        }
        $result = MerchantSettingService::getValueByKey($merchantId, $key);
        if (!$result){
            throw new BaseResponseException('单品购买功能尚未开启！');
        }
    }

    /**
     * 获取单品分类
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
     * 获取单品指定分类的商品
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDishesGoods()
    {
        $merchantId = request('merchant_id');
        $categoryId = request('category_id');
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


   //点菜操作

    public function dishesSettle()
    {
        $userId = request()->get('current_user')->id;
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
//        $dishesList = request('goods_list');
        $dishesList=   [['id'=>'1','number'=>2],['id'=>2,'number'=>'2']];
        $merchantId = request('merchant_id');
        if (empty($dishesList)){
            throw new ParamInvalidException('单品列表为空');
        }
        if(sizeof($dishesList) < 1){
            throw new ParamInvalidException('参数不合法1');
        }
        foreach ($dishesList as $item) {
            if(!isset($item['id']) || !isset($item['number'])){
                throw new ParamInvalidException('参数不合法2');
            }
        }
        $merchant = Merchant::findOrFail($merchantId);
        $dishes = new Dishes();
        $dishes->oper_id = $merchant->oper_id;
        $dishes->merchant_id = $merchant->id;
        $dishes->user_id = $userId;
        $dishes->save();

        foreach ($dishesList as $item){
            $dishesGoods = DishesGoods::findOrFail($item['id']);
            if ($dishesGoods['oper_id'] !== $merchant->oper_id){
                continue;
            }
            $dishesItem = new DishesItem();
            $dishesItem->oper_id = $merchant->oper_id;
            $dishesItem->merchant_id = $merchant->id;
            $dishesItem->dishes_id = $dishes->id;
            $dishesItem->user_id = $userId;
            $dishesItem->dishes_goods_id = $item['id'];
            $dishesItem->number = $item['number'];
            $dishesItem->dishes_goods_sale_price = $dishesGoods['sale_price'];
            $dishesItem->dishes_goods_logo = $dishesGoods['logo'];
            $dishesItem->dishes_goods_name = $dishesGoods['name'];
            $dishesItem->save();
        }
        return Result::success($dishes);
    }
}