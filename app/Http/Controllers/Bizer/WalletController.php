<?php

namespace App\Http\Controllers\Bizer;


use App\Exceptions\BaseResponseException;
use App\Exports\WalletBillExport;
use App\Http\Controllers\Controller;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Oper\OperService;
use App\Modules\Order\OrderService;
use App\Modules\Wallet\BankCardService;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdraw;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;

class WalletController extends Controller
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

    public function getBillDetail()
    {
        $id = request('id');
        if (!$id) throw new BaseResponseException('id不能为空');
        $walletBill = WalletService::getBillById($id);
        if (empty($walletBill)) throw new BaseResponseException('该钱包流水不存在');

        $walletBill->oper_name = OperService::getNameById($walletBill->origin_id);
        $walletBill->balance_unfreeze_time = '';

        $orderOrWithdrawData = null;
        if (in_array($walletBill->type, [WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_SUBORDINATE_REFUND, WalletBill::TYPE_OPER, WalletBill::TYPE_OPER_REFUND, WalletBill::TYPE_BIZER, WalletBill::TYPE_BIZER_REFUND])) {
            $feeSplittingRecord = FeeSplittingService::getFeeSplittingRecordById($walletBill->obj_id);
            $order = OrderService::getById($feeSplittingRecord->order_id);
            $orderOrWithdrawData = $order;
            $walletBalanceUnfreezeRecord = WalletService::getBalanceUnfreezeRecordByFeeSplittingId($walletBill->obj_id);
            $walletBill->balance_unfreeze_time = !empty($walletBalanceUnfreezeRecord) ? $walletBalanceUnfreezeRecord->created_at->format('Y-m-d H:i:s') : '';
        }

        if (in_array($walletBill->type, [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED])) {
            $walletWithdraw = WalletWithdrawService::getWalletWithdrawById($walletBill->obj_id);
            $orderOrWithdrawData = $walletWithdraw;
        }

        return Result::success([
            'billData' => $walletBill,
            'orderOrWithdrawData' => $orderOrWithdrawData,
        ]);
    }

    /**
     * 获取银行列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBankList()
    {
        $list = BankCardService::getBankList(true);
        return Result::success($list);
    }
}