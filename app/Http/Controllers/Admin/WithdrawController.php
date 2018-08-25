<?php

namespace App\Http\Controllers\Admin;


use App\Exports\WalletWithdrawExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\OperService;
use App\Modules\User\UserService;
use App\Modules\Wallet\WalletWithdraw;
use App\Modules\Wallet\WalletWithdrawService;
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

    /**
     * admin 提现记录
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function withdrawRecord()
    {
        $pageSize = request('pageSize', 15);
        $param = self::getRequest();
        $data = WalletWithdrawService::getWithdrawRecords($param, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取request 构建查询参数数组
     * @return array
     */
    private static function getRequest()
    {
        $originType = request('originType', 0);
        $mobile = request('mobile', '');
        $merchantName = request('merchantName', '');
        $operName = request('operName', '');
        $merchantId = request('merchantId', '');
        $operId = request('operId', '');
        $withdrawNo = request('withdrawNo', '');
        $bankCardType = request('bankCardType', '');
        $startDate = request('startDate', '');
        $endDate = request('endDate', '');
        $status = request('status', '');

        $originIdArr = [];
        $originId = '';
        if ($originType == WalletWithdraw::ORIGIN_TYPE_USER) {
            if ($mobile) {
                $originIdArr = UserService::getUserColumnArrayByMobile($mobile, 'id');
            }
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
            if ($merchantName) {
                $originIdArr = MerchantService::getMerchantColumnArrayByMerchantName($merchantName, 'id');
            }
            if ($merchantId) $originId = $merchantId;
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_OPER) {
            if ($operName) {
                $originIdArr = OperService::getOperColumnArrayByOperName($operName, 'id');
            }
            if ($operId) $originId = $operId;
        } else {
            $originIdArr = [];
        }

        $param = compact('originType', 'originId', 'originIdArr', 'withdrawNo', 'bankCardType', 'startDate', 'endDate', 'status');
        return $param;
    }

    public function exportExcel()
    {
        $pageSize = request('pageSize', 15);
        $originType = request('originType', 0);
        $param = self::getRequest();
        $query = WalletWithdrawService::getWithdrawRecords($param, $pageSize, true);

        return (new WalletWithdrawExport($query, $originType))->download('提现记录.xlsx');
    }
}