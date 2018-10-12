<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Order\OrderService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
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

        if(
            $feeSplittingRecord->order_profit_amount == $profitAmount
            && $feeSplittingRecord->ratio == $ratio
            && $feeSplittingRecord->amount == floor($profitAmount * $ratio) / 100
        ){
            throw new BaseResponseException('该分润数据正确, 不需要重新分润');
        }

        DB::beginTransaction();
        try {
            // 更新分润记录
            $newFeeSplittingRecord = FeeSplittingService::updateFeeSplittingRecord($feeSplittingRecord, $profitAmount, $ratio);
            // 判断钱包需要修复的金额
            $amount = $newFeeSplittingRecord->amount - $feeSplittingRecord->amount;
            $wallet = WalletService::getWalletInfoByOriginInfo($feeSplittingRecord->origin_id, $feeSplittingRecord->origin_type);
            // 钱包流水表 添加钱包流水记录
            if ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_SELF) {
                $type = WalletBill::TYPE_SELF;
            } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_PARENT) {
                $type = WalletBill::TYPE_SUBORDINATE;
            } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_OPER) {
                $type = WalletBill::TYPE_OPER;
            } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_BIZER) {
                $type = WalletBill::TYPE_BIZER;
            } else {
                throw new ParamInvalidException('分润类型参数错误');
            }
            if($amount > 0){
                if($newFeeSplittingRecord->status == FeeSplittingRecord::STATUS_FREEZE){
                    WalletService::addFreezeBalance($wallet, $amount, $type, $newFeeSplittingRecord->id);
                }else {
                    WalletService::addBalance($wallet, $amount, $type, $newFeeSplittingRecord->id);
                }
            }else if($amount < 0) {
                WalletService::minusBalance($wallet, -$amount, $type, $newFeeSplittingRecord->id);
            }

//            WalletService::addFreezeBalance()
            // 更新钱包和流水
//            $wallet = WalletService::getWalletInfoByOriginInfo($feeSplittingRecord->origin_id, $feeSplittingRecord->origin_type);
//            WalletService::updateWalletAndWalletBill($wallet, $feeSplittingRecord, $newFeeSplittingRecord);

            DB::commit();
            return Result::success($newFeeSplittingRecord);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('重新分润失败的LOG', ['data' => $e]);
            throw new BaseResponseException('重新分润失败');
        }
    }
}