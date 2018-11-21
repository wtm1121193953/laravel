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

class CsMerchantCategoryService extends BaseService
{
    public static function getList(array $params = [], bool $getWithQuery = false)
    {

        $query = CsMerchantCategory::select('*')
            ->when(!empty($params['cs_merchant_id']),function (Builder $query) use ($params){
                $query->where('cs_merchant_id','=', $params['cs_merchant_id']);
            })
            ->when(isset($params['cs_category_parent_id']),function (Builder $query) use ($params){
                $query->where('cs_category_parent_id','=', $params['cs_category_parent_id']);
            })
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

            $merchant_cat = CsMerchantCategory::where('cs_merchant_id',$cs_merchant_id)
                ->where('platform_category_id',$cat['id'])->first();

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
        $list->each(function ($item){
            $item->sub = CsMerchantCategory::where('cs_category_level',2)
                ->where('cs_category_parent_id',$item->id)
                ->get();
        });
        return $list;
    }


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

        $rs = $merchant_cat->save();
        if ($rs) {
            return $merchant_cat->status;
        }

    }
}