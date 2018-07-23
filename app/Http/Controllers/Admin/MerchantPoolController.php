<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 15:21
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantPoolService;
use App\Result;

class MerchantPoolController extends Controller
{

    /**
     * 获取商户池列表
     */
    public function getList()
    {
        $keyword = request('keyword');
        $data = MerchantPoolService::getList($keyword);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $merchant = MerchantPoolService::detail($id);
        return Result::success($merchant);
    }

}