<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletBatchService;
use App\Result;

class WalletBatchController extends Controller
{
    /**
     * 提现批次管理列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function walletBatchList()
    {
        $batchNo = request('batchNo', '');
        $type = request('type', '');
        $status = request('status', '');
        $start = request('startDate', '');
        $end = request('endDate', '');
        $pageSize = request('pageSize', 15);

        $start = $start ? date('Y-m-d 00:00:00', strtotime($start)) : '';
        $end = $end ? date('Y-m-d 23:59:59', strtotime($end)) : '';

        $params = compact('batchNo', 'type', 'status', 'start', 'end');
        $data = WalletBatchService::getWalletBatch($params, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}