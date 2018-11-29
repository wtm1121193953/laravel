<?php
/**
 * 结算数据【新】
 */

namespace App\Http\Controllers\Admin;


use App\Exports\SettlementPlatformExport;
use App\Http\Controllers\Controller;
use App\Modules\Order\OrderService;
use App\Modules\Settlement\SettlementPlatform;
use App\Result;
use Illuminate\Support\Facades\Log;
use App\Modules\Settlement\SettlementPlatformService;

class SettlementPlatformController extends Controller
{

    /**
     * 获取结算列表【新】
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $merchant_name = request('merchant_name');
        $merchant_id = request('merchant_id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $settlementCycleType = request('settlement_cycle_type');
        $isAutoSettlement = request('is_auto_settlement');
        $uri = request()->getRequestUri();
        // 商户类型
        $merchantType = (strpos($uri,'csPlatforms')) ? SettlementPlatform::MERCHANT_TYPE_CS : ((request('merchant_type')) ? request('merchant_type') : '');
        $startTime = microtime(true);
        $data = SettlementPlatformService::getListForSaas([
            'merchant_name' => $merchant_name,
            'merchant_id' => $merchant_id,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'settlementCycleType' => $settlementCycleType,
            'isAutoSettlement' => $isAutoSettlement,
            'merchantType'   => $merchantType
        ]);
        $endTime = microtime(true);

        Log::debug('耗时: ', ['start time' => $startTime, 'end time' => $endTime, '耗时: ' => $endTime - $startTime]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 下载Excel
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel()
    {
        $merchant_name = request('merchant_name');
        $merchant_id = request('merchant_id');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $status = request('status');
        $settlementCycleType = request('settlement_cycle_type');
        $isAutoSettlement = request('is_auto_settlement');

        $uri = request()->getRequestUri();
        $merchantType = (strpos($uri,'csDownload')) ? SettlementPlatform::MERCHANT_TYPE_CS : ((request('merchant_type')) ? request('merchant_type') : '');

        $query = SettlementPlatformService::getListForSaas([
            'merchant_name' => $merchant_name,
            'merchant_id' => $merchant_id,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'settlementCycleType' => $settlementCycleType,
            'isAutoSettlement' => $isAutoSettlement,
            'merchantType'   => $merchantType
        ],true);

        return (new SettlementPlatformExport($query))->download('结算报表.xlsx');
    }

    public function modifyStatus()
    {
        $id = request()->get('id');
        $settlement = SettlementPlatformService::getByIdModifyStatus($id);
        return Result::success($settlement);
    }


    public function reBatchAgain()
    {
        $id = request()->get('id');
        $settlement = SettlementPlatformService::genBatchAgain($id);
        return Result::success($settlement);
    }

    public function getSettlementOrders()
    {
        $this->validate(request(), [
            'settlement_id' => 'required|integer|min:1'
        ]);

        $settlementId   = request()->get('settlement_id');
        $data = OrderService::getListByPlatformSettlementId($settlementId);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }


}