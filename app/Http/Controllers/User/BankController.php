<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Bank;
use App\Result;


class BankController extends Controller
{
    /**
     * 获取银行列表
     * Author:  Jerry
     * Date:    180831
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getList()
    {
        $list = Bank::where('status',Bank::STATUS_USABLE)
                ->select('id','name')
                ->get();
        return Result::success( ['list' => $list] );
    }
}
