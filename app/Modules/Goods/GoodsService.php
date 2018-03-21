<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/19
 * Time: 19:52
 */

namespace App\Modules\Goods;


use Illuminate\Database\Eloquent\Collection;

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
        $goods->specs = GoodsSpec::where('goods_id', $goods->id)->get();
        $goods->defaultSpec = $goods->specs->firstWhere('id', $goods->default_spec_id);
        $goods->stock = self::getGoodsStock($goods->specs);
        return $goods;
    }

    /**
     * 获取商品库存
     * @param Collection|Goods|int $goodsOrSpecs 商品对象|商品ID|商品规格集合
     * @return int
     */
    public static function getGoodsStock($goodsOrSpecs)
    {
        if(is_int($goodsOrSpecs) || $goodsOrSpecs instanceof Goods){
            return GoodsSpec::where('goods_id', is_int($goodsOrSpecs) ? $goodsOrSpecs : $goodsOrSpecs->id)
                ->sum('stock');
        }else if(!$goodsOrSpecs instanceof Collection){
            $goodsOrSpecs = collect($goodsOrSpecs);
        }
        return $goodsOrSpecs->sum('stock');
    }
}