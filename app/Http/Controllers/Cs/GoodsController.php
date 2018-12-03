<?php

namespace App\Http\Controllers\Cs;


use App\DataCacheService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\Cs\CsGood;
use App\Modules\Cs\CsGoodService;
use App\Modules\Cs\CsMerchantCategoryService;
use App\Result;
use Illuminate\Http\Request;

class GoodsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        parent::__init();
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('cs_platform_cat_id_level1','');
        $params['cs_platform_cat_id_level2'] = request('cs_platform_cat_id_level2','');
        $params['cs_merchant_id'] = $this->_cs_merchant_id;
        $params['sort'] = 2;
        $data = CsGoodService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取子分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCat()
    {
        parent::__init();
        $parent_id = request('parent_id');
        $cs_merchant_id = $this->_cs_merchant_id;
        $rt = CsMerchantCategoryService::getSubCat($cs_merchant_id,$parent_id);

        $useful_cat = DataCacheService::getPlatformCatsUseful();
        $data = [];
        if ($rt) {
            foreach ($rt as $k=>$v) {
                $d = [];
                if (empty($useful_cat[$k])) {
                    $d['label'] = $v . '（平台已禁用）';
                    $d['value'] = $k;
                    $d['disabled'] = true;
                } else {
                    $d['label'] = $v;
                    $d['value'] = $k;
                }
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
        parent::__init();
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $cs_merchant_id = $this->_cs_merchant_id;
        $goods = CsGoodService::detail(request('id'),$cs_merchant_id);
        if(empty($goods)){
            throw new DataNotFoundException('商品信息不存在或已删除');
        }

        $goods->detail_imgs = $goods->detail_imgs ? explode(',', $goods->detail_imgs) : [];
        $goods->certificate1 = $goods->certificate1 ? explode(',', $goods->certificate1) : [];
        $goods->certificate2 = $goods->certificate2 ? explode(',', $goods->certificate2) : [];
        $goods->certificate3 = $goods->certificate3 ? explode(',', $goods->certificate3) : [];

        return Result::success($goods);
    }


    /**
     * 添加商品信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        parent::__init();
        $request->validate([
            'goods_name' => 'required',
            'market_price' => 'required',
            'price' => 'required',
            'cs_platform_cat_id_level1' => 'required',
            'cs_platform_cat_id_level2' => 'required',
        ]);


        $cs_goods = new CsGood();
        $cs_goods->goods_name = $request->goods_name;
        $cs_goods->oper_id = $this->_oper_id;
        $cs_goods->cs_merchant_id = $this->_cs_merchant_id;
        $cs_goods->cs_platform_cat_id_level1 = $request->cs_platform_cat_id_level1;
        $cs_goods->cs_platform_cat_id_level2 = $request->cs_platform_cat_id_level2;
        $cs_goods->market_price = $request->market_price;
        $cs_goods->price = $request->price;
        $cs_goods->stock = $request->stock;
        $cs_goods->logo = $request->logo;
        $cs_goods->detail_imgs = implode(',',$request->detail_imgs);
        $cs_goods->summary = $request->summary;
        $cs_goods->certificate1 = implode(',',$request->certificate1);
        $cs_goods->certificate2 = implode(',',$request->certificate2);
        $cs_goods->certificate3 = implode(',',$request->certificate3);
        $cs_goods->status = CsGood::STATUS_OFF;
        $cs_goods->audit_status = CsGood::AUDIT_STATUS_AUDITING;

        $rs = $cs_goods->save();

        if ($rs) {

            return Result::success('添加成功');
        } else {
            throw new BaseResponseException('添加失败');
        }
    }

    /**
     * 编辑商品
     */
    public function edit(Request $request)
    {
        parent::__init();
        $request->validate([
            'id' =>'required',
            'goods_name' => 'required',
            'market_price' => 'required',
            'price' => 'required',
            'cs_platform_cat_id_level1' => 'required',
            'cs_platform_cat_id_level2' => 'required',
        ]);
        $id = $request->id;
        $cs_merchant_id = $this->_cs_merchant_id;
        $cs_goods = CsGood::findOrFail($id);
        if ($cs_goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }

        $cs_goods->goods_name = $request->goods_name;
        $cs_goods->oper_id = $this->_oper_id;
        $cs_goods->cs_merchant_id = $this->_cs_merchant_id;
        $cs_goods->cs_platform_cat_id_level1 = $request->cs_platform_cat_id_level1;
        $cs_goods->cs_platform_cat_id_level2 = $request->cs_platform_cat_id_level2;
        $cs_goods->market_price = $request->market_price;
        $cs_goods->price = $request->price;
        $cs_goods->stock = $request->stock;
        $cs_goods->logo = $request->logo;
        $cs_goods->detail_imgs = implode(',',$request->detail_imgs);
        $cs_goods->summary = $request->summary;
        $cs_goods->certificate1 = implode(',',$request->certificate1);
        $cs_goods->certificate2 = implode(',',$request->certificate2);
        $cs_goods->certificate3 = implode(',',$request->certificate3);
        $cs_goods->status = CsGood::STATUS_OFF;
        $cs_goods->audit_status = CsGood::AUDIT_STATUS_AUDITING;

        $rs = $cs_goods->save();

        if ($rs) {

            return Result::success('修改成功');
        } else {
            throw new BaseResponseException('添加失败');
        }
        return Result::success();
    }

    /**
     * 审核通过后的编辑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fastEdit(Request $request)
    {
        parent::__init();
        $this->validate($request, [
            'id' =>'required',
            'market_price' => 'required',
            'price' => 'required',
            'cs_platform_cat_id_level1' => 'required',
            'cs_platform_cat_id_level2' => 'required',
        ]);
        $id = $request->id;
        $cs_merchant_id = $this->_cs_merchant_id;
        $cs_goods = CsGood::findOrFail($id);
        if ($cs_goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }


        $cs_goods->cs_platform_cat_id_level1 = $request->cs_platform_cat_id_level1;
        $cs_goods->cs_platform_cat_id_level2 = $request->cs_platform_cat_id_level2;
        $cs_goods->market_price = $request->market_price;
        $cs_goods->price = $request->price;
        $cs_goods->stock = $request->stock;
        $cs_goods->logo = $request->logo;
        $cs_goods->summary = $request->summary;

        $rs = $cs_goods->save();

        if ($rs) {
            return Result::success('修改成功');
        } else {
            throw new BaseResponseException('添加失败');
        }
    }

    /**
     * 修改上下架
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus()
    {
        parent::__init();
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        //$cs_merchant_id = request()->get('current_user')->merchant_id;
        $cs_merchant_id = $this->_cs_merchant_id;

        $csGoods = CsGoodService::changeStatus(request('id'), $cs_merchant_id);

        return Result::success($csGoods);
    }

    /**
     * 删除
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        parent::__init();
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $cs_merchant_id = $this->_cs_merchant_id;
        $goods = CsGoodService::del(request('id'),$cs_merchant_id);

        return Result::success($goods);
    }

    /**
     * 修改排序
     */
    public function modifySort()
    {
        parent::__init();
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'sort' => 'required|integer|min:0',

        ]);
        $cs_merchant_id = $this->_cs_merchant_id;
        $goods = CsGoodService::detail(request('id'),$cs_merchant_id);
        $goods->sort = request('sort',0);
        $goods->save();
        return Result::success($goods);
    }

}