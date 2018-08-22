<?php

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Result;

class WalletController extends Controller
{
    /**
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

        $param = compact('billNo', 'startDate', 'endDate', 'typeArr');
        $merchantId = request()->get('current_user')->merchant_id;
        $data = WalletService::getWalletBillListByOriginInfo($param, $merchantId, WalletBill::ORIGIN_TYPE_MERCHANT, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function exportExcel()
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

        $param = compact('billNo', 'startDate', 'endDate', 'typeArr');
        $merchantId = request()->get('current_user')->merchant_id;
        $query = WalletService::getWalletBillListByOriginInfo($param, $merchantId, WalletBill::ORIGIN_TYPE_MERCHANT, $pageSize, true);

    }
}