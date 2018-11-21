<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/21/021
 * Time: 17:34
 */
namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Cs\CsGoodService;
use App\Modules\Cs\CsMerchantCategoryService;
use App\Modules\Cs\CsPlatformCategory;
use App\Modules\Cs\CsPlatformCategoryService;
use App\Result;
use Illuminate\Http\Request;

class CsGoodsController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $params['goods_name'] = request('goods_name','');
        $params['cs_platform_cat_id_level1'] = request('cs_platform_cat_id_level1','');
        $params['cs_platform_cat_id_level2'] = request('cs_platform_cat_id_level2','');
        $params['oper_id'] = request()->get('current_user')->oper_id;
        $params['id'] = request('id',0);
        $params['status'] = request('status',[]);
        $params['audit_status'] = request('auditStatus',[]);
        $params['with_merchant'] = 1;

        $data = CsGoodService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

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
}