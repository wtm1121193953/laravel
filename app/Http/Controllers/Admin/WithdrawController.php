<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
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
            case 'bizer':
                $originType = 4;
                break;
            case 'cs':
                $originType = 5;
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
                $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();
                break;
        }

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
        $param = self::getWithdrawListParamsFromRequest();
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
    private static function getWithdrawListParamsFromRequest()
    {
        $originType = request('originType', 0);
        $userMobile = request('mobile', '');
        $merchantName = request('merchantName', '');
        $operName = request('operName', '');
        $merchantId = request('merchantId', '');
        $operId = request('operId', '');
        $bizerId = request('bizerId', '');
        $bizerMobile = request('bizerMobile', '');
        $withdrawNo = request('withdrawNo', '');
        $bankCardType = request('bankCardType', '');
        $start = request('startDate', '');
        $end = request('endDate', '');
        $status = request('status', '');
        $batchId = request('batchId', '');

        $start = $start ? date('Y-m-d 00:00:00', strtotime($start)) : '';
        $end = $end ? date('Y-m-d 23:59:59', strtotime($end)) : '';

        $originId = '';
        if ($originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
            $originId = $merchantId;
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_OPER) {
            $originId = $operId;
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_BIZER) {
            $originId = $bizerId;
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_CS) {
            $originId = $merchantId;
        }

        $param = compact('originType', 'originId', 'withdrawNo', 'bankCardType', 'start', 'end', 'status', 'userMobile', 'merchantName', 'operName', 'bizerMobile', 'batchId');
        return $param;
    }

    /**
     * 提现记录导出
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel()
    {
        $pageSize = request('pageSize', 15);
        $originType = request('originType', 0);
        $param = self::getWithdrawListParamsFromRequest();
        $query = WalletWithdrawService::getWithdrawRecords($param, $pageSize, true);

        return (new WalletWithdrawExport($query, $originType))->download('提现记录.xlsx');
    }

    /**
     * 提现记录详情
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function withdrawDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $data = WalletWithdrawService::getWalletWithdrawDetailById($id);

        return Result::success($data);
    }

    /**
     * admin提现审核
     * @throws \Exception
     */
    public function audit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required'
        ]);

        $id = request('id');
        $status = request('status');
        $remark = request('remark', '');

        $walletWithdraw = WalletWithdrawService::getWalletWithdrawById($id);
        if (empty($walletWithdraw)) throw new BaseResponseException('改提现记录不存在');

        if ($status == WalletWithdraw::STATUS_AUDIT) {
            $this->validate(request(), ['batchId' => 'required']);
            $batchId = request('batchId');
            WalletWithdrawService::auditSuccess($walletWithdraw, $batchId, $remark);
        } elseif ($status == WalletWithdraw::STATUS_AUDIT_FAILED) {
            WalletWithdrawService::auditFailed($walletWithdraw, $remark);
        } else {
            throw new BaseResponseException('状态错误');
        }
        return Result::success();
    }

    /**
     * 打款成功操作 或者 批量打款成功操作
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function paySuccess()
    {
        $ids = request('ids');
        $batchId = request('batchId');
        $status = WalletWithdraw::STATUS_AUDIT;  // 审核成功的才能打款
        if ($batchId) {
            $withdrawQuery = WalletWithdrawService::getWithdrawRecords(compact('batchId', 'status'), 15, true);
            $ids = $withdrawQuery->select('id')->get()->pluck('id')->toArray();
        }
        if (empty($ids)) {
            throw new ParamInvalidException('没有需要打款的订单');
        }
        WalletWithdrawService::paySuccess($ids);
        return Result::success();
    }

    /**
     * 打款失败操作 或者 批量打款失败操作
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function payFail()
    {
        $ids = request('ids');
        $batchId = request('batchId');
        $status = WalletWithdraw::STATUS_AUDIT;  // 审核成功的才能打款
        $remark = request('remark');
        if ($batchId) {
            $withdrawQuery = WalletWithdrawService::getWithdrawRecords(compact('batchId', 'status'), 15, true);
            $ids = $withdrawQuery->select('id')->get()->pluck('id')->toArray();
        }
        if (empty($ids)) {
            throw new ParamInvalidException('没有需要打款的订单');
        }
        WalletWithdrawService::payFail($ids, $remark);
        return Result::success();
    }
}