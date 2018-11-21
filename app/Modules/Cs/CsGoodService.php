<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 14:39
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\BaseResponseException;
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

        $id = array_get($params, 'id');
        $query = CsGood::select('*')
            ->when($id,function (Builder $query) use ($id){
                $query->where('id','=', $id);
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

    public static function getGoodsList($merchant_id,$isSaleAsc,$isPriceAsc,$firstLevelId,$secondLevelId){
        $query = CsGood::where('cs_merchant_id',$merchant_id)
        ->where('audit_status',2);
        if (!empty($secondLevelId)){
            $query->where('cs_merchant_cat_id_level2',$secondLevelId);
        }
        elseif (!empty($firstLevelId)){
            $query->where('cs_merchant_cat_id_level1',$firstLevelId);
        }
        if (!empty($isSaleAsc)){
            if ($isSaleAsc == '1'){
                $query->orderBy('sale_num', 'ASC');
            }
            else{
                $query->orderBy('sale_num', 'DESC');
            }
        }

        if (!empty($isPriceAsc)){

            if ($isPriceAsc == '1'){
                $query->orderBy('market_price', 'ASC');
            }
            else{
                $query->orderBy('market_price', 'DESC');
            }
        }
        return $query->get();
    }
}