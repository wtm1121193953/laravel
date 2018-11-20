<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 14:39
 */
namespace App\Modules\Cs;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;

class CsGoodService extends BaseService
{

    /**
     * 查询商品列表
     * @param array $params
     * @param bool $getWithQuery
     * @return mixed
     */
    public static function getList(array $params = [], bool $getWithQuery = false)
    {

        $query = CsGood::select('*')
            ->when(!empty($params['goods_name']),function (Builder $query) use ($params){
                $query->where('goods_name','like', "%{$params['goods_name']}%");
            })
            ->when(!empty($params['cs_merchant_cat_id_level1']),function (Builder $query) use ($params){
                $query->where('cs_merchant_cat_id_level1','=', $params['cs_merchant_cat_id_level1']);
            })
            ->when(!empty($params['cs_merchant_cat_id_level2']),function (Builder $query) use ($params){
                $query->where('cs_merchant_cat_id_level2','=', $params['cs_merchant_cat_id_level2']);
            })
        ;

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->paginate();
            $data->each(function ($item) {

            });

            return $data;
        }
    }
}