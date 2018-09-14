<?php

namespace App\Http\Controllers\Bizer;


use App\Exports\WalletBillExport;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdraw;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;

class WalletController
{
    /**
     * 获取业务员 钱包流水
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBillList()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->id;
        $originType = WalletBill::ORIGIN_TYPE_BIZER;
        $param = compact('billNo', 'startDate', 'endDate', 'type', 'originId', 'originType');
        $data = WalletService::getBillList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);
        // 获取提现统计
        $withdrawStatistics = WalletWithdrawService::getWithdrawTotalAmountAndCount([
            'originType' => $originType,
            'originId' => $originId,
            'status' => WalletWithdraw::STATUS_WITHDRAW,
        ]);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amountBalance' => number_format($wallet->balance + $wallet->freeze_balance, 2, '.', ''),
            'balance' => $wallet->balance,
            'freezeBalance' => $wallet->freeze_balance,
            'hasBalanced' => number_format($withdrawStatistics['amount'], 2, '.', ''),
        ]);
    }

    /**
     * 业务员交易流水导出
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportBillExcel()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->id;
        $originType = WalletBill::ORIGIN_TYPE_BIZER;
        $param = compact('billNo', 'startDate', 'endDate', 'type', 'originId', 'originType');
        $query = WalletService::getBillList($param, $pageSize, true);

        return (new WalletBillExport($query, $originType))->download('业务员交易流水.xlsx');
    }
}