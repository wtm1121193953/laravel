<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 14:39
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\DataCacheService;
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

        $query = CsGood::select('*')
            ->when(!empty($params['id']),function (Builder $query) use ($params){
                $query->where('id','=', $params['id']);
            })
            ->when(!empty($params['oper_id']),function (Builder $query) use ($params){
                $query->where('oper_id','=', $params['oper_id']);
            })
            ->when(!empty($params['cs_merchant_id']),function (Builder $query) use ($params){
                $query->where('cs_merchant_id','=', $params['cs_merchant_id']);
            })
            ->when(!empty($params['goods_name']),function (Builder $query) use ($params){
                $query->where('goods_name','like', "%{$params['goods_name']}%");
            })
            ->when(!empty($params['cs_platform_cat_id_level1']),function (Builder $query) use ($params){
                $query->where('cs_platform_cat_id_level1','=', $params['cs_platform_cat_id_level1']);
            })
            ->when(!empty($params['cs_platform_cat_id_level2']),function (Builder $query) use ($params){
                $query->where('cs_platform_cat_id_level2','=', $params['cs_platform_cat_id_level2']);
            })
            ->when(!empty($params['status']),function (Builder $query) use ($params){
                $query->whereIn('status', $params['status']);
            })
            ->when(!empty($params['audit_status']),function (Builder $query) use ($params){
                $query->whereIn('audit_status', $params['audit_status']);
            })
            ->when(!empty($params['cs_merchant_ids']),function (Builder $query) use ($params){
                $query->whereIn('cs_merchant_id', $params['cs_merchant_ids']);
            })
            ->wher(!empty($params['sort']) && !empty($params['order']),function (Builder $query) use ($params) {
                $query->orderBy($params['sort'],$params['order']);
            })
            ->when(!empty($params['sort']),function (Builder $query) use ($params){
                if ($params['sort'] == 1) {
                    $query->orderBy('sort','desc');
                } elseif ($params['sort'] == 2) {
                    $query->orderBy('created_at','desc');
                }  else {
                    $query->orderBy('sort','desc');
                }

            })
            ->when(!empty($params['with_merchant']),function (Builder $query) use ($params){
                $query->with('cs_merchant:id,name');
            })
        ;

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->paginate();
            $all_cats = DataCacheService::getPlatformCats();
            $data->each(function ($item) use ($all_cats) {

                $item->cs_platform_cat_id_level1_name = !empty($all_cats[$item->cs_platform_cat_id_level1])?$all_cats[$item->cs_platform_cat_id_level1]:'';
                $item->cs_platform_cat_id_level2_name = !empty($all_cats[$item->cs_platform_cat_id_level2])?$all_cats[$item->cs_platform_cat_id_level2]:'';

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

        $goods->save();

        return $goods;
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

    /**
     * 商户查看详情
     * @param int $id
     * @param int $cs_merchant_id
     * @return CsGood
     */
    public static function detail(int $id, int $cs_merchant_id)
    {
        $goods = CsGood::findOrFail($id);
        if ($goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }
        return $goods;
    }

    /**
     * 运营中心查看详情
     * @param int $id
     * @param int $oper_id
     * @return CsGood
     */
    public static function operDetail(int $id, int $oper_id)
    {

        $goods = CsGood::findOrFail($id);
        if ($goods->oper_id != $oper_id) {
            throw new BaseResponseException('参数错误2');
        }
        return $goods;
    }

    public static function getGoodsList($merchant_id,$isSaleAsc,$isPriceAsc,$firstLevelId,$secondLevelId){
        $query = CsGood::where('cs_merchant_id',$merchant_id)
        ->where('audit_status',2);
        if (!empty($secondLevelId) && $secondLevelId > 0){
            $query->where('cs_platform_cat_id_level2',$secondLevelId);
        }
        elseif (!empty($firstLevelId) && $secondLevelId > 0){
            $query->where('cs_platform_cat_id_level1',$firstLevelId);
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