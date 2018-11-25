<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/21/021
 * Time: 17:34
 */
namespace App\Http\Controllers\Oper;

use App\DataCacheService;
use App\Exceptions\BaseResponseException;
use App\Exports\CsGoodsExport;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGood;
use App\Modules\Cs\CsGoodService;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Cs\CsPlatformCategoryService;
use App\Result;

class CsGoodsController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $params = [];
        $cs_merchant_name = request('merchant_name','');
        if (!empty($cs_merchant_name)) {
            $params['cs_merchant_ids'] = CsMerchantService::getIdsByName($cs_merchant_name);
        }
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('cs_platform_cat_id_level1','');
        $params['cs_platform_cat_id_level2'] = request('cs_platform_cat_id_level2','');
        $params['oper_id'] = request()->get('current_user')->oper_id;
        $params['id'] = request('id',0);
        $params['status'] = request('status',[]);
        $params['audit_status'] = request('auditStatus',[]);
        $params['with_merchant'] = 1;
        $params['cs_merchant_id'] = request('cs_merchant_id',0);
        $params['sort'] = 2;
        $data = CsGoodService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出
     */
    public function download()
    {
        $params = [];
        $cs_merchant_name = request('merchant_name','');
        if (!empty($cs_merchant_name)) {
            $params['cs_merchant_ids'] = CsMerchantService::getIdsByName($cs_merchant_name);
        }
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('cs_platform_cat_id_level1','');
        $params['cs_platform_cat_id_level2'] = request('cs_platform_cat_id_level2','');
        $params['oper_id'] = request()->get('current_user')->oper_id;
        $params['id'] = request('id',0);
        $params['status'] = request('status',[]);
        $params['audit_status'] = request('auditStatus',[]);
        $params['with_merchant'] = 1;
        $params['cs_merchant_id'] = request('cs_merchant_id',0);

        $query = CsGoodService::getList($params,true);
        return (new CsGoodsExport($query))->download('商品列表.xlsx');

    }

    /**
     * 获取子分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCat()
    {

        $parent_id = request('parent_id',0);
        $rt = CsPlatformCategoryService::getSubCat($parent_id);

        $data = [['label'=>'全部','value'=>'0']];
        if ($rt) {
            foreach ($rt as $k=>$v) {
                $d['label'] = $v;
                $d['value'] = $k;
                $data[] = $d;
            }
        }

        return Result::success($data);

    }

    /**
     * 获取商品详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $oper_id = request()->get('current_user')->oper_id;
        $goods = CsGoodService::operDetail($id,$oper_id);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已删除');
        }

        $goods->detail_imgs = $goods->detail_imgs ? explode(',', $goods->detail_imgs) : [];
        $goods->certificate1 = $goods->certificate1 ? explode(',', $goods->certificate1) : [];
        $goods->certificate2 = $goods->certificate2 ? explode(',', $goods->certificate2) : [];
        $goods->certificate3 = $goods->certificate3 ? explode(',', $goods->certificate3) : [];

        $all_cats = DataCacheService::getPlatformCats();
        $goods->cs_platform_cat_id_level1_name = $all_cats[$goods->cs_platform_cat_id_level1];
        $goods->cs_platform_cat_id_level2_name = $all_cats[$goods->cs_platform_cat_id_level2];
        $goods->status_name = CsGood::statusName($goods->status);
        $goods->audit_status_name = CsGood::auditStatusName($goods->audit_status);

        return Result::success($goods);
    }

    /**
     * 审核
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $type = request('type');
        $oper_id = request()->get('current_user')->oper_id;
        $cs_goods = CsGood::findOrFail($id);

        if ($cs_goods->oper_id != $oper_id) {
            throw new BaseResponseException('参数错误');
        }

        if ($type == 1) {
            //审核通过自动上架
            $cs_goods->status = CsGood::STATUS_ON;
            $cs_goods->audit_status = CsGood::AUDIT_STATUS_SUCCESS;
        } else {
            //审核不通过自动下架
            $cs_goods->status = CsGood::STATUS_OFF;
            $cs_goods->audit_status = CsGood::AUDIT_STATUS_FAIL;
            $cs_goods->audit_suggestion = request('audit_suggestion','');
        }
        $rs = $cs_goods->save();
        return Result::success('审核成功');


    }
}