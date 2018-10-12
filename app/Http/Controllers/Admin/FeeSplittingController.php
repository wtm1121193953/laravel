<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Order\OrderService;
use App\Modules\Wallet\WalletService;
use App\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeSplittingController extends Controller
{
    /**
     * 获取分润记录列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $originId = request('originId', '');
        $originType = request('originType', '');
        $orderId = request('orderId', '');
        $orderNo = request('orderNo', '');
        $type = request('type', '');
        $status = request('status', '');
        $pageSize = request('pageSize', 15);

        $params = compact('originId', 'originType', 'orderId', 'orderNo', 'type', 'status');
        $data = FeeSplittingService::getFeeSplittingRecordList($params, $pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 重新分润
     * @return \Illuminate\Http\JsonResponse
     */
    public function ReFeeSplitting()
    {
        $this->validate(request(), [
            'id' => 'required|min:1|integer',
        ]);
        $id = request('id');
        $feeSplittingRecord = FeeSplittingService::getById($id);
        if (empty($feeSplittingRecord)) throw new BaseResponseException('该分润记录不存在');

        $order = OrderService::getById($feeSplittingRecord->order_id);
        if (empty($order)) throw new BaseResponseException('分润的订单不存在');
        // 获取订单利润
        $profitAmount = OrderService::getProfitAmount($order);
        // 获取分润比例
        $ratio = FeeSplittingService::getReFeeSplittingRatio($feeSplittingRecord, $order);
        if (!$ratio) throw new BaseResponseException('分润比例为空');

        DB::beginTransaction();
        try {
            // 更新分润记录
            $newFeeSplittingRecord = FeeSplittingService::updateFeeSplittingRecord($feeSplittingRecord, $profitAmount, $ratio);
            // 更新钱包和流水
            $wallet = WalletService::getWalletInfoByOriginInfo($feeSplittingRecord->origin_id, $feeSplittingRecord->origin_type);
            WalletService::updateWalletAndWalletBill($wallet, $feeSplittingRecord, $newFeeSplittingRecord);

            DB::commit();
            return Result::success($newFeeSplittingRecord);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('重新分润失败的LOG', ['data' => $e]);
            throw new BaseResponseException('重新分润失败');
        }
    }
}