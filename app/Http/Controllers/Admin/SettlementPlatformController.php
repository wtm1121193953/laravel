<?php
/**
 * 结算数据【新】
 */

namespace App\Http\Controllers\Admin;


use App\Exports\SettlementPlatformExport;
use App\Http\Controllers\Controller;
use App\Result;
use Illuminate\Support\Facades\Log;
use App\Modules\Settlement\SettlementPlatformService;
use App\Exceptions\DataNotFoundException;

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


        $startTime = microtime(true);
        $data = SettlementPlatformService::getListForSaas([
            'merchant_name' => $merchant_name,
            'merchant_id' => $merchant_id,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status
        ]);
        $endTime = microtime(true);

        Log::info('耗时: ', ['start time' => $startTime, 'end time' => $endTime, '耗时: ' => $endTime - $startTime]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }



    public function detail()
    {

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


        $query = SettlementPlatformService::getListForSaas([
            'merchant_name' => $merchant_name,
            'merchant_id' => $merchant_id,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status
        ],true);

        return (new SettlementPlatformExport($query))->download('结算报表.xlsx');
    }

    public function modifyStatus()
    {
        $id = request()->get('id');
        $settlement = SettlementPlatformService::getByIdModifyStatus($id);
        return Result::success($settlement);
    }

    public function getSettlementOrders()
    {
        $settlementId   = request()->get('settlement_id');
        $merchantId     = request()->get('merchant_id');
        $settlement = SettlementPlatformService::getByIdAndMerchantId($settlementId, $merchantId);
        if(empty($settlement)){
            throw new DataNotFoundException('结算单不存在');
        }
        $MerchantController = new \App\Http\Controllers\Merchant\SettlementPlatformController();
        return $MerchantController->getOrdersByService( $settlementId );
    }


}