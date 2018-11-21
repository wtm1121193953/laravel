<?php

namespace App\Http\Controllers\Cs;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGood;
use App\Modules\Cs\CsGoodService;
use App\Modules\Cs\CsMerchantCategory;
use App\Modules\Cs\CsMerchantCategoryService;
use App\Result;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('cs_platform_cat_id_level1','');
        $params['cs_platform_cat_id_level2'] = request('cs_platform_cat_id_level2','');
        $params['cs_merchant_id'] = 1000000000;
        $data = CsGoodService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getSubCat()
    {

        $parent_id = request('parent_id');
        $cs_merchant_id = 1000000000;
        $rt = CsMerchantCategoryService::getSubCat($cs_merchant_id,$parent_id);

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

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        //$cs_merchant_id = request()->get('current_user')->merchant_id;
        $cs_merchant_id = 1000000000;
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
     * 添加数据
     */
    public function add(Request $request)
    {
        $request->validate([
            'goods_name' => 'required',
            'market_price' => 'required',
            'price' => 'required',
        ]);


        $cs_goods = new CsGood();
        $cs_goods->goods_name = $request->goods_name;
        $cs_goods->cs_merchant_id = 1000000000;
        $cs_goods->cs_platform_cat_id_level1 = 1;
        $cs_goods->cs_platform_cat_id_level2 = 3;
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
     * 编辑
     */
    public function edit(Request $request)
    {
        $request->validate([
            'id' =>'required',
            'goods_name' => 'required',
            'market_price' => 'required',
            'price' => 'required',
        ]);
        $id = $request->id;
        //$cs_merchant_id = request()->get('current_user')->merchant_id;
        $cs_merchant_id = 1000000000;
        $cs_goods = CsGood::findOrFail($id);
        if ($cs_goods->cs_merchant_id != $cs_merchant_id) {
            throw new BaseResponseException('参数错误2');
        }

        $cs_goods->goods_name = $request->goods_name;
        $cs_goods->cs_merchant_id = 1000000000;
        $cs_goods->cs_platform_cat_id_level1 = 1;
        $cs_goods->cs_platform_cat_id_level2 = 3;
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
     * 修改状态
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        //$cs_merchant_id = request()->get('current_user')->merchant_id;
        $cs_merchant_id = 1000000000;

        $new_status = CsGoodService::changeStatus(request('id'),$cs_merchant_id);

        return Result::success($new_status);
    }

    /**
     * 删除
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        //$cs_merchant_id = request()->get('current_user')->merchant_id;
        $cs_merchant_id = 1000000000;
        $goods = CsGoodService::del(request('id'),$cs_merchant_id);

        return Result::success($goods);
    }

    /**
     * 团购商品排序
     */
    public function saveOrder(){
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $type = request('type', 'up');
        $merchantId = request()->get('current_user')->merchant_id;
        GoodsService::changeSort(request('id'), $merchantId, $type);
        return Result::success();
    }

}