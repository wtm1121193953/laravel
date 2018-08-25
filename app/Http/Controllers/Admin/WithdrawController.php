<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletWithdraw;
use App\Result;

class WithdrawController extends Controller
{

    /**
     * 获取汇总数据
     */
    public function dashboard()
    {
        $originType = request('originType');
        $timeType = request('timeType');

        // todo 查询提现汇总数据

        return Result::success([
            'totalAmount' => rand(0, 100000000),
            'totalCount' => rand(0, 10000000),
            'successAmount' => rand(0, 10000000),
            'successCount' => rand(0, 1000000),
            'failAmount' => rand(0, 1000000),
            'failCount' => rand(0, 100000),
        ]);
    }

    public function withdrawRecord()
    {
        $originType = request('originType', 0);
        $mobile = request('mobile', '');

        if ($originType = WalletWithdraw::ORIGIN_TYPE_USER) {

        }
    }
}