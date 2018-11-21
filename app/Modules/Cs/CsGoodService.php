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
use App\Modules\Goods\Goods;
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


    public static function changeStatus(int $id, int $cs_merchant_id)
    {
        if ($id<0 || $cs_merchant_id<0) {
            throw new BaseResponseException('参数错误1');
        }

        $goods = CsGood::findOrFail($id);
        if ($goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }

        if ($goods->audit_status != CsGood::AUDIT_STATUS_SUCCESS) {
            throw new BaseResponseException('商品未审核通过');
        }

        $goods->status = $goods->status == CsGood::STATUS_ON?CsGood::STATUS_OFF:CsGood::STATUS_ON;

        $rs = $goods->save();
        if ($rs) {
            return $goods->status;
        }

    }

    public static function del(int $id, int $cs_merchant_id)
    {
        if ($id<0 || $cs_merchant_id<0) {
            throw new BaseResponseException('参数错误1');
        }

        $goods = CsGood::findOrFail($id);
        if ($goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }

        return $goods->delete();
    }

    public static function detail(int $id, int $cs_merchant_id)
    {
        $goods = CsGood::findOrFail($id);
        if ($goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }
        return $goods;
    }
}