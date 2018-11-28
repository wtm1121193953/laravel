<?php

namespace App\Http\Controllers\Admin;


use App\Exports\WalletBillExport;
use App\Exports\WalletExport;
use App\Http\Controllers\Controller;
use App\Modules\Wallet\BankCardService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;
use App\Result;

class WalletController extends Controller
{
    /**
     * 获取request 构建查询参数数组
     * @return array
     */
    private static function getWalletListParamsFromRequest()
    {
        $originType = request('originType', 0);
        $userMobile = request('mobile', '');
        $bizerMobile = request('bizerMobile', '');
        $bizerName = request('bizerName', '');
        $merchantName = request('merchantName', '');
        $operName = request('operName', '');
        $merchantId = request('merchantId', '');
        $operId = request('operId', '');
        $userId = request('userId', '');
        $bizerId = request('bizerId', '');
        $status = request('status', '');

        $originId = '';
        if ($originType == Wallet::ORIGIN_TYPE_MERCHANT) {
            $originId = $merchantId;
        } elseif ($originType == Wallet::ORIGIN_TYPE_OPER) {
            $originId = $operId;
        } elseif ($originType == Wallet::ORIGIN_TYPE_USER) {
            $originId = $userId;
        } elseif ($originType == Wallet::ORIGIN_TYPE_BIZER) {
            $originId = $bizerId;
        } elseif ($originType == Wallet::ORIGIN_TYPE_CS) {
            $originId = $merchantId;
        }

        $param = compact('originType', 'originId', 'status', 'userMobile', 'merchantName', 'operName', 'bizerMobile', 'bizerName');
        return $param;
    }

    /**
     * 获取钱包列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWalletList()
    {
        $pageSize = request('pageSize', 15);
        $param = self::getWalletListParamsFromRequest();
        $data = WalletService::getWalletList($param, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出钱包列表
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function walletListExportExcel()
    {
        $pageSize = request('pageSize', 15);
        $originType = request('originType', 0);
        $param = self::getWalletListParamsFromRequest();
        $query = WalletService::getWalletList($param, $pageSize, true);

        return (new WalletExport($query, $originType))->download('账户列表.xlsx');
    }

    /**
     * 解冻 或者 冻结 钱包
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changeWalletStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $wallet = WalletService::changeWalletStatus($id);
        return Result::success($wallet);
    }

    /**
     * admin 获取交易流水
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWalletBillList()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $walletId = request('walletId', 0);
        $pageSize = request('pageSize', 15);

        if ($type && $type == 7) {
            $type = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
        }

        $param = compact('billNo', 'startDate', 'endDate', 'type', 'walletId');
        $data = WalletService::getBillList($param, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * admin 导出交易流水
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function walletBillExportExcel()
    {
        $billNo = request('billNo', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $type = request('type', 0);
        $walletId = request('walletId', 0);
        $originType = request('originType', 0);
        $pageSize = request('pageSize', 15);

        if ($type && $type == 7) {
            $type = [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED];
        }

        $param = compact('billNo', 'startDate', 'endDate', 'type', 'walletId');
        $query = WalletService::getBillList($param, $pageSize, true);

        return (new WalletBillExport($query, $originType))->download('交易流水.xlsx');
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