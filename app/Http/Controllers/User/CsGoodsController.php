<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 10:02 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGoodService;
use App\Result;

class CsGoodsController extends Controller
{

    /**
     * 获取商户下商品
     */
    public function getAllGoods(){
        $merchant_id = request('merchant_id');
        $isSaleAsc = request('is_sale_asc');  //是否销量升序
        $isPriceAsc = request('is_price_asc');//是否价格升序
        $firstLevelId = request('first_level_id');//一级分类id
        $secondLevelId = request('second_level_id');//二级分类id
        $list = CsGoodService::getGoodsList($merchant_id,$isSaleAsc,$isPriceAsc,$firstLevelId,$secondLevelId);
        return Result::success($list);
    }
}