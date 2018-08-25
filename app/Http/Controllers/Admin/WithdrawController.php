<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
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

    public function withdrawRecord()
    {
        $originType = request('originType', 0);
        $mobile = request('mobile', '');

        if ($originType = WalletWithdraw::ORIGIN_TYPE_USER) {

        }
    }
}