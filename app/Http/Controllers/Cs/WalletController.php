<?php

namespace App\Http\Controllers\Cs;


use App\Exceptions\BaseResponseException;
use App\Exports\WalletBillExport;
use App\Exports\WalletConsumeQuotaRecordExport;
use App\Exports\WalletTpsCreditExport;
use App\Http\Controllers\Controller;
use App\Modules\Cs\CsMerchantService;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Order\OrderService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\WalletBill;
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

        if ($type) {
            if ($type == 1) {
                $type = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
            } elseif ($type == 2) {
                $type = [WalletBill::TYPE_SELF, WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_OPER];
            } elseif ($type == 3) {
                $type = [WalletBill::TYPE_SELF_CONSUME_REFUND, WalletBill::TYPE_SUBORDINATE_REFUND, WalletBill::TYPE_OPER_REFUND];
            } else {
                $type = [];
            }
        }

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_CS;
        $param = compact('billNo', 'startDate', 'endDate', 'type', 'originId', 'originType');
        $data = WalletService::getBillList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amountBalance' => number_format($wallet->balance + $wallet->freeze_balance, 2, '.', ''),
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

        if ($type) {
            if ($type == 1) {
                $type = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
            } elseif ($type == 2) {
                $type = [WalletBill::TYPE_SELF, WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_OPER];
            } elseif ($type == 3) {
                $type = [WalletBill::TYPE_SELF_CONSUME_REFUND, WalletBill::TYPE_SUBORDINATE_REFUND, WalletBill::TYPE_OPER_REFUND];
            } else {
                $type = [];
            }
        }

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('billNo', 'startDate', 'endDate', 'type', 'originId', 'originType');
        $query = WalletService::getBillList($param, $pageSize, true);

        return (new WalletBillExport($query, $originType))->download('商户交易流水.xlsx');
    }

    /**
     * 获取钱包流水明细
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBillDetail()
    {
        $id = request('id');
        if (!$id) throw new BaseResponseException('id不能为空');
        $walletBill = WalletService::getBillById($id);
        if (empty($walletBill)) throw new BaseResponseException('该钱包流水不存在');

        $walletBill->merchant_name = CsMerchantService::getNameById($walletBill->origin_id);
        $walletBill->balance_unfreeze_time = '';

        $orderOrWithdrawData = null;
        if (in_array($walletBill->type, [WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_SUBORDINATE_REFUND])) {
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
        $data = ConsumeQuotaService::getConsumeQuotaRecordList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);

        $param['startDate'] = date('Y-m-01 00:00:00');
        $param['endDate'] = date('Y-m-t 23:59:59');
        $thisMonthQuotaSum = ConsumeQuotaService::getConsumeQuotaRecordList($param, $pageSize, true)->sum('consume_quota');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            // 累计获得下级贡献值
            'shareConsumeQuotaSum' => $wallet->share_consume_quota+$wallet->share_freeze_consume_quota,
            // 本月累计获得下级贡献值
            'thisMonthQuotaSum'   => (float)$thisMonthQuotaSum,
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
        $query = ConsumeQuotaService::getConsumeQuotaRecordList($param, $pageSize, true);

        return (new WalletConsumeQuotaRecordExport($query))->download('我的贡献值记录表.xlsx');
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

        return Result::success($consumeQuotaRecord);
    }

    /**
     * 获取商户 tps积分列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTpsCreditList()
    {
        $consumeQuotaNo = request('consumeQuotaNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('consumeQuotaNo', 'startDate', 'endDate', 'status', 'originId', 'originType');
        $data = ConsumeQuotaService::getConsumeQuotaRecordList($param, $pageSize);
        // 获取钱包信息
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);
        $theMonthShareTpsCredit = ConsumeQuotaService::getConsumeQuotaRecordList(compact('originType', 'originId'), $pageSize, true)->sum('sync_tps_credit');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalShareTpsCredit' => $wallet->total_share_tps_credit,
            'theMonthShareTpsCredit' => $theMonthShareTpsCredit,
        ]);
    }

    /**
     * 商户导出TPS积分记录
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportTpsCreditExcel()
    {
        $consumeQuotaNo = request('consumeQuotaNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', 0);
        $pageSize = request('pageSize', 15);

        $originId = request()->get('current_user')->merchant_id;
        $originType = WalletBill::ORIGIN_TYPE_MERCHANT;
        $param = compact('consumeQuotaNo', 'startDate', 'endDate', 'status', 'originId', 'originType');
        $query = ConsumeQuotaService::getConsumeQuotaRecordList($param, $pageSize, true);

        return (new WalletTpsCreditExport($query))->download('我的TPS积分记录表.xlsx');
    }

    /**
     * 获取 商户积分明细
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTpsCreditDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $tpsCredit = ConsumeQuotaService::getConsumeQuotaRecordById($id);
        if (empty($tpsCredit)) throw new BaseResponseException('该消费额记录不存在');

        return Result::success($tpsCredit);
    }
}