<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 13:33
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Goods\GoodsService;
use App\Result;

class GoodsController extends Controller
{

    public function getList()
    {
        $this->validate(request(), [
            'merchant_id' => 'required|integer|min:1',
        ]);
        $merchant_id = request('merchant_id');
        $list = GoodsService::userGoodsList($merchant_id);
        return Result::success(['list' => $list]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');

        $detail = GoodsService::userGoodsDetail($id);

        return Result::success($detail);
    }
}