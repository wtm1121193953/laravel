<?php

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Exports\WalletBillExport;
use App\Exports\WalletConsumeQuotaRecordExport;
use App\Http\Controllers\Controller;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\OrderService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\WalletWithdrawService;
use App\Result;

class WalletController extends Controller
{
    /**
     * 获取商户的交易流水
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBillList()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $pageSize = request('pageSize', 15);

        $typeArr = [];
        if ($type) {
            if ($type == 1) {
                $typeArr = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
            } elseif ($type == 2) {
                $typeArr = [WalletBill::TYPE_SELF, WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_OPER];
            } elseif ($type == 3) {
                $typeArr = [WalletBill::TYPE_SELF_CONSUME_REFUND, WalletBill::TYPE_SUBORDINATE_REFUND, WalletBill::TYPE_OPER_REFUND];
            } else {
                $typeArr = [];
            }
        }

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('billNo', 'startDate', 'endDate', 'typeArr', 'originId', 'originType');
        $data = WalletService::getBillList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amountBalance' => number_format($wallet->balance + $wallet->freeze_balance, 2),
            'balance' => $wallet->balance,
            'freezeBalance' => $wallet->freeze_balance,
        ]);
    }

    /**
     * 导出商户的交易流水
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportBillExcel()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $pageSize = request('pageSize', 15);

        $typeArr = [];
        if ($type) {
            if ($type == 1) {
                $typeArr = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
            } elseif ($type == 2) {
                $typeArr = [WalletBill::TYPE_SELF, WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_OPER];
            } elseif ($type == 3) {
                $typeArr = [WalletBill::TYPE_SELF_CONSUME_REFUND, WalletBill::TYPE_SUBORDINATE_REFUND, WalletBill::TYPE_OPER_REFUND];
            } else {
                $typeArr = [];
            }
        }

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('billNo', 'startDate', 'endDate', 'typeArr', 'originId', 'originType');
        $query = WalletService::getBillList($param, $pageSize, true);

        return (new WalletBillExport($query))->download('商户交易流水.xlsx');
    }

    /**
     * 获取钱包流水明细
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBillDetail()
    {
        $id = request('id');
        if (!$id) throw new BaseResponseException('id不能为空');
        $walletBill = WalletService::getWalletBillById($id);
        if (empty($walletBill)) throw new BaseResponseException('该钱包流水不存在');

        $walletBill->merchant_name = MerchantService::getNameById($walletBill->origin_id);

        $orderOrWithdrawData = null;
        if (in_array($walletBill->type, [WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_SUBORDINATE_REFUND])) {
            $feeSplittingRecord = FeeSplittingService::getFeeSplittingRecordById($walletBill->obj_id);
            $order = OrderService::getById($feeSplittingRecord->order_id);
            $orderOrWithdrawData = $order;
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
     * 获取消费额记录列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConsumeQuotaList()
    {
        $consumeQuotaNo = request('consumeQuotaNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('consumeQuotaNo', 'startDate', 'endDate', 'status', 'originId', 'originType');
        $data = ConsumeQuotaService::getWalletConsumeQuotaRecordList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'consumeQuota' => $wallet->consume_quota,
            'freezeConsumeQuota' => $wallet->freeze_consume_quota,
        ]);
    }

    /**
     * 导出消费额记录
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportConsumeQuotaRecordExcel()
    {
        $consumeQuotaNo = request('consumeQuotaNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('consumeQuotaNo', 'startDate', 'endDate', 'status', 'originId', 'originType');
        $query = ConsumeQuotaService::getWalletConsumeQuotaRecordList($param, $pageSize);

        return (new WalletConsumeQuotaRecordExport($query))->download('消费额记录表.xlsx');
    }

    /**
     * 获取消费额记录详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConsumeQuotaDetail()
    {
        $id = request('id');
        if (!$id) throw new BaseResponseException('id不能为空');
        $consumeQuotaRecord = ConsumeQuotaService::getConsumeQuotaRecordById($id);
        if (empty($consumeQuotaRecord)) throw new BaseResponseException('该消费额记录不存在');

        if ($consumeQuotaRecord->status == WalletConsumeQuotaRecord::STATUS_FREEZE) {
            $consumeQuotaRecord->time = $consumeQuotaRecord->created_at->addDays(1);
        } elseif ($consumeQuotaRecord->status == WalletConsumeQuotaRecord::STATUS_REPLACEMENT) {
            $consumeQuotaUnfreezeRecord = ConsumeQuotaService::getConsumeQuotaUnfreezeRecordById($consumeQuotaRecord->id);
            $consumeQuotaRecord->time = $consumeQuotaUnfreezeRecord->created_at;
        } elseif ($consumeQuotaRecord->status == WalletConsumeQuotaRecord::STATUS_REFUND) {
            $order = OrderService::getById($consumeQuotaRecord->order_id);
            $consumeQuotaRecord->time = $order->refund_time;
        } else {
            $consumeQuotaRecord->time = null;
        }

        return Result::success($consumeQuotaRecord);
    }
}