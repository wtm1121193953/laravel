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
use EasyWeChat\Kernel\Messages\Card;
use Illuminate\Support\Carbon;

class WithdrawController extends Controller
{

    /**
     * 获取汇总数据
     */
    public function dashboard()
    {
        // all 全部  user-用户  merchant-商户  oper-运营中心
        $originTypeFlag = request('originType', 'all');
        // 时间类型 all-全部  today-今日 yesterday-昨日 month-本月 last-month: 上个月, other:其他时间
        $timeTypeFlag = request('timeType', 'today');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $operId = request('operId');
        $merchantId = request('merchantId');

        switch ($originTypeFlag){
            case 'user':
                $originType = 1;
                break;
            case 'merchant':
                $originType = 2;
                break;
            case 'oper':
                $originType = 3;
                break;
            default:
                $originType = null;
        }
        switch ($timeTypeFlag) {
            case 'all':
                $start = null;
                $end = null;
                break;
            case 'today':
                $start = Carbon::now()->startOfDay();
                $end = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $start = Carbon::now()->subDay()->startOfDay();
                $end = $start->copy()->endOfDay();
                break;
            case 'month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
            case 'lastMonth':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = $start->copy()->endOfMonth();
                break;
            default:
                $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfMonth();
                break;
        }

        // todo 查询提现汇总数据
        $params = [
            'start' => $start,
            'end' => $end,
            'originType' => $originType,
            'originId' => $originType == 2 ? $merchantId : ($originType == 3 ? $operId : null),
        ];

        $totalData = WalletWithdrawService::getWithdrawTotalAmountAndCount($params);
        $params['status'] = 3;
        $successData = WalletWithdrawService::getWithdrawTotalAmountAndCount($params);
        $params['status'] = [4, 5];
        $failData = WalletWithdrawService::getWithdrawTotalAmountAndCount($params);

        return Result::success([
            'totalAmount' => $totalData['amount'],
            'totalCount' => $totalData['count'],
            'successAmount' => $successData['amount'],
            'successCount' => $successData['count'],
            'failAmount' => $failData['amount'],
            'failCount' => $failData['count'],
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