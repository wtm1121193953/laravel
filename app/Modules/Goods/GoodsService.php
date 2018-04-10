<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 19:52
 */

namespace App\Modules\Goods;



class GoodsService
{

    /**
     * 获取商品详情
     * @param Goods|int $goods
     * @return Goods
     */
    public static function getDetail($goods)
    {
        if(is_int($goods)){
            $goods = Goods::findOrFail($goods);
        }
        return $goods;
    }

}