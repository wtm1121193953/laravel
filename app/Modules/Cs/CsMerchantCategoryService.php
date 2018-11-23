<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/20/020
 * Time: 18:25
 */
namespace App\Modules\Cs;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CsMerchantCategoryService extends BaseService
{
    /**
     * 超市商户分类列表
     * @param array $params
     * @param bool $getWithQuery
     * @return CsMerchantCategory|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params = [], bool $getWithQuery = false)
    {

        $query = CsMerchantCategory::select('*')
            ->when(!empty($params['cs_merchant_id']),function (Builder $query) use ($params){
                $query->where('cs_merchant_id','=', $params['cs_merchant_id']);
            })
            ->when(isset($params['cs_category_parent_id']),function (Builder $query) use ($params){
                $query->where('cs_category_parent_id','=', $params['cs_category_parent_id']);
            })
            ->orderBy('sort','asc')
        ;

        if ($getWithQuery) {
            return $query;
        } else {

            $data = $query->paginate();
            return $data;
        }
    }

    /**
     * 获取商户的子分类
     * @param int $cs_merchant_id
     * @param int $parent_id
     * @return array
     */
    public static function getSubCat(int $cs_merchant_id, int $parent_id=0)
    {

        if ($cs_merchant_id <= 0) {
            return [];
        }

        $rs = CsMerchantCategory::where('cs_category_parent_id','=',$parent_id)
            ->where('cs_merchant_id','=',$cs_merchant_id)
            ->get();
        $rt = [];
        if ($rs) {
            foreach ($rs as $v) {
                $rt[$v['platform_category_id']] = $v['cs_cat_name'];
            }
        }

        return $rt;

    }

    /**
     * 同步平台分类
     * @param int $cs_merchant_id
     * @return bool
     */
    public static function synPlatFormCat(int $cs_merchant_id)
    {

        if ($cs_merchant_id <= 0) {
            return false;
        }
        $platform_cat = CsPlatformCategoryService::getAll();
        if (empty($platform_cat)) {
            return false;
        }

        foreach ($platform_cat as $cat) {


            $merchant_cat = self::getMerchantCat($cs_merchant_id, $cat['id']);

            if ($merchant_cat) {
                $merchant_cat->cs_merchant_id = $cs_merchant_id;
                $merchant_cat->platform_category_id =$cat['id'];
                $merchant_cat->cs_cat_name =$cat['cat_name'];
                $merchant_cat->cs_category_parent_id =$cat['parent_id'];
                $merchant_cat->cs_category_level =$cat['level'];
                $merchant_cat->platform_cat_status =$cat['status'];

                $merchant_cat->save();
            } else {

                $merchant_cat = new CsMerchantCategory();

                $merchant_cat->cs_merchant_id = $cs_merchant_id;
                $merchant_cat->platform_category_id =$cat['id'];
                $merchant_cat->cs_cat_name =$cat['cat_name'];
                $merchant_cat->cs_category_parent_id =$cat['parent_id'];
                $merchant_cat->cs_category_level =$cat['level'];
                $merchant_cat->platform_cat_status =$cat['status'];
                $merchant_cat->status = CsMerchantCategory::STATUS_OFF;
                $merchant_cat->sort = self::getMaxSort($cs_merchant_id) + 1;
                $merchant_cat->save();
            }

        }
        return true;
    }

    /**
     * 获取商户分类里的最大排序
     * @param int $cs_merchant_id
     * @return bool|number
     */
    public static function getMaxSort(int $cs_merchant_id)
    {
        if ($cs_merchant_id<=0) {
            return false;
        }
        return CsMerchantCategory::where('cs_merchant_id',$cs_merchant_id)->max('sort');
    }

    public static function getTree($merchantId){
        $list = CsMerchantCategory::where('cs_category_level',1)
            ->where('cs_merchant_id',$merchantId)
            ->get();


        $totalCategory =new CsMerchantCategory();
        $totalCategory->cs_cat_name = '全部分类';
        $totalCategory->status = CsMerchantCategory::STATUS_ON;
        $totalCategory->id = 0;
        $query = Array();
        array_push($query,$totalCategory);
        foreach ($list as $item){
                $item->sub = CsMerchantCategory::where('cs_category_level',2)
                    ->where('cs_category_parent_id',$item->id)
                    ->get();
            array_push($query,$item);
        }
        $query = collect($query);
        $query->each(function ($item){
            $totalCategory =new CsMerchantCategory();
            $totalCategory->cs_cat_name = '全部分类';
            $totalCategory->status = CsMerchantCategory::STATUS_ON;
            $totalCategory->id = 0;
            $totalSub = Array();
            if ($item->id == 0){
                $totalCategory->cs_category_parent_id = 0;
                array_push($totalSub,$totalCategory);
                $item->sub = $totalSub;
            }
            else{
                $totalCategory->id = $item->id;
                array_push($totalSub,$totalCategory);
                if (!empty($item->sub)){
                    foreach ($item->sub as $subItem){
                        array_push($totalSub,$subItem);
                    }
                }
                $item->sub = $totalSub;
            }
        });


        return $query;
    }


    /**
     * 商品分类上下架切换
     * @param int $id
     * @param int $cs_merchant_id
     * @return int
     */
    public static function changeStatus(int $id, int $cs_merchant_id)
    {
        if ($id<0 || $cs_merchant_id<0) {
            throw new BaseResponseException('参数错误1');
        }

        $merchant_cat = CsMerchantCategory::findOrFail($id);
        if ($merchant_cat->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }

        $merchant_cat->status = $merchant_cat->status == CsMerchantCategory::STATUS_ON?CsMerchantCategory::STATUS_OFF:CsMerchantCategory::STATUS_ON;

        DB::beginTransaction();
        try {
            $merchant_cat->save();
            if ($merchant_cat->status == CsMerchantCategory::STATUS_OFF) {
                //如果下架分类对应子分类和相关的商品
                CsMerchantCategory::where('cs_merchant_id',$cs_merchant_id)
                    ->where('cs_category_parent_id',$merchant_cat->platform_category_id)
                    ->update(['status'=>CsMerchantCategory::STATUS_OFF])
                ;
                if ($merchant_cat->cs_category_level == 1) {
                    CsGood::where('cs_platform_cat_id_level1',$merchant_cat->platform_category_id)
                        ->update(['status'=>CsGood::STATUS_OFF])
                    ;
                } elseif ($merchant_cat->cs_category_level == 2) {
                    CsGood::where('cs_platform_cat_id_level2',$merchant_cat->platform_category_id)
                        ->update(['status'=>CsGood::STATUS_OFF])
                    ;
                }
            }
            DB::commit();
            return $merchant_cat->status;
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * 获取超市分类信息
     * @param int $cs_merchant_id
     * @param int $platform_category_id
     * @return CsMerchantCategory
     */
    public static function getMerchantCat(int $cs_merchant_id, int $platform_category_id)
    {
        return CsMerchantCategory::where('cs_merchant_id',$cs_merchant_id)
            ->where('platform_category_id',$platform_category_id)->first();
    }


    /**
     * 修改排序
     * @param $platform_category_id 平台分类ID
     * @param $cs_merchant_id
     * @param string $type
     * @throws \Exception
     */
    public static function changeSort($platform_category_id, $cs_merchant_id, $type = 'up')
    {
        if ($type == 'down'){
            $option = '>';
            $order = 'asc';
        }else{
            $option = '<';
            $order = 'desc';
        }

        $merchant_cat = self::getMerchantCat($cs_merchant_id, $platform_category_id);
        if (empty($merchant_cat)){
            throw new BaseResponseException('该单品分类不存在');
        }
        $merchant_cat_exchange = CsMerchantCategory::where('cs_merchant_id', $cs_merchant_id)
            ->where('cs_category_parent_id',$merchant_cat->cs_category_parent_id)
            ->where('sort', $option, $merchant_cat->sort)
            ->orderBy('sort', $order)
            ->first();
        if (empty($merchant_cat_exchange)){
            throw new BaseResponseException('交换位置的单品分类不存在');
        }

        $item = $merchant_cat_exchange->sort;
        $merchant_cat_exchange->sort = $merchant_cat->sort;
        $merchant_cat->sort = $item;

        DB::beginTransaction();
        $res1 = $merchant_cat->save();
        $res2 = $merchant_cat_exchange->save();
        if ($res1 && $res2){
            DB::commit();
        }else{
            DB::rollBack();
            throw new BaseResponseException('交换位置失败');
        }
    }
}