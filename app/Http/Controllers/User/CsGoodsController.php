<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/20
 * Time: 10:02 PM
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGood;
use App\Modules\Cs\CsGoodService;
use App\Result;

class CsGoodsController extends Controller
{

    /**
     * 获取商户下商品
     */
    public function getAllGoods(){

        $this->validate(request(),[
            'merchant_id' => 'required|integer|min:1'
        ]);

        $isSale = request('is_sale');  //是否销量1升序 2降序
        $isPrice = request('is_price');//是否价格1升序 2降序


        $params['cs_merchant_id'] = request('merchant_id',0);
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('first_level_id','');
        $params['cs_platform_cat_id_level2'] = request('second_level_id','');
        $params['status'] = [CsGood::STATUS_ON];
        $params['audit_status'] =[CsGood::AUDIT_STATUS_SUCCESS];
        $params['pageSize'] = request('pageSize',15);
        if (!empty($isSale)) {

            $params['sort'] = 'sale_num';
            $params['order'] = $isSale == 1 ?'asc':'desc';
        } elseif (!empty($isPrice)) {
            $params['sort'] = 'price';
            $params['order'] = $isPrice == 1 ?'asc':'desc';
        } else {
            $params['sort'] = '1';
        }

        $data = CsGoodService::getList($params);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);

    }
}