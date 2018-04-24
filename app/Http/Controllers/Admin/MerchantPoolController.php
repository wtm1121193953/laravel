<?php
/**
 * Created by PhpStorm.
 * User: Evan Lee
 * Date: 2018/4/24
 * Time: 15:21
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Result;

class MerchantPoolController extends Controller
{

    /**
     * 获取商户池列表
     */
    public function getList()
    {
        $data = Merchant::where('contract_status', Merchant::CONTRACT_STATUS_NO)
            ->orderByDesc('id')->paginate();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}