<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Jobs\ConsumeQuotaSyncToTpsJob;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\Tps\TpsBindService;
use App\Modules\User\UserService;
use App\Support\TpsApi;
use App\Support\Utils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 消费额相关service
 * Class ConsumeQuotaService
 * @package App\Modules\Wallet
 */
class ConsumeQuotaService extends BaseService
{

    /**
     * 添加消费额
     * @param Order $order
     * @throws \Exception
     */
    public static function addFreezeConsumeQuota(Order $order)
    {
        // 1. 获取用户自己的钱包
        $user = UserService::getUserById($order->user_id);
        $wallet = WalletService::getWalletInfo($user);
        DB::beginTransaction();
        try {
            // 计算消费额
            $consumeQuota = $order->pay_price;
            // 计算tps消费额 (美元)
            $tpsConsumeQuota = Utils::getDecimalByNotRounding($consumeQuota / 6 / 6.5, 8);
            // 计算tps积分
            $tpsCredit = Utils::getDecimalByNotRounding($tpsConsumeQuota / 4, 8);
            // 计算订单纯利润
            $orderProfitAmount = OrderService::getProfitAmount($order) -  FeeSplittingService::getOrderFeeSplittingAmountByOrderId($order->id);
            // 计算要分给tps消费额对应的盈利
            $consumeQuotaProfit = Utils::getDecimalByNotRounding($orderProfitAmount / 2, 2);

            // 添加自己的消费额记录, 并计算是否需要同步积分
            $syncTpsCredit = floor(($wallet->total_tps_credit + $tpsCredit) - floor($wallet->total_tps_credit));
            self::addConsumeQuotaRecord([
                'wallet' => $wallet,
                'order' => $order,
                'order_profit_amount' => $orderProfitAmount,
                'type' => WalletConsumeQuotaRecord::TYPE_SELF,
                'consume_quota' => $consumeQuota,
                'consume_quota_profit' => $consumeQuotaProfit,
                'tps_consume_quota' => $tpsConsumeQuota,
                'tps_credit' => $tpsCredit,
                'sync_tps_credit' => $syncTpsCredit,
            ]);

            // 判断是否需要给上级返积分
            $parent = InviteUserService::getParent($order->user_id);
            $hundred = floor(($wallet->total_tps_credit + $tpsCredit - floor($wallet->total_tps_credit / 100) * 100) / 100);
            if (!empty($parent) && $hundred > 0) {
                // 只有当前tps积分与累计消费积分累加后超过一百, 才给上级反tps积分
                $parentSyncTpsCredit = $hundred * 50;
                $parentWallet = WalletService::getWalletInfo($parent);

                // 添加上级消费额记录
                self::addConsumeQuotaRecord([
                    'wallet' => $parentWallet,
                    'order' => $order,
                    'order_profit_amount' => $orderProfitAmount,
                    'type' => WalletConsumeQuotaRecord::TYPE_SUBORDINATE,
                    'sync_tps_credit' => $parentSyncTpsCredit,
                ]);

                // 添加 上级钱包中的tps积分 上级添加tps累计积分
                $parentWallet->total_share_tps_credit = DB::raw('total_share_tps_credit +' . $parentSyncTpsCredit);
                $parentWallet->save();
            }

            // 更新用户钱包信息
            // 获取用户自己的消费额 和 订单的tps积分 和 需要同步的积分 并更新钱包表
            $wallet->freeze_consume_quota = DB::raw('freeze_consume_quota +' . $consumeQuota);  // 当月冻结消费额
            $wallet->total_consume_quota = DB::raw('total_consume_quota +' . $consumeQuota);   // 个人累计消费额
            $wallet->total_tps_credit = DB::raw('total_tps_credit +' . $tpsCredit);         // 个人消费累计tps积分
            $wallet->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 解冻消费额
     * @param Order $order
     */
    public static function unfreezeConsumeQuota(Order $order)
    {
        // 1. 解冻自己的消费额
        // 2. 解冻上级的消费额
        $walletConsumeQuotaRecords = WalletConsumeQuotaRecord::where('order_id', $order->id)->get();
        foreach ($walletConsumeQuotaRecords as $walletConsumeQuotaRecord) {
            // 1.找到钱包
            $wallet = WalletService::getWalletInfoByOriginInfo($walletConsumeQuotaRecord->origin_id, $walletConsumeQuotaRecord->origin_type);
            // 2.添加消费额解冻记录
            self::addConsumeQuotaUnfreezeRecord($walletConsumeQuotaRecord, $wallet);
            // 3.更新钱包信息
            $wallet->consume_quota = DB::raw('consume_quota + ' . $walletConsumeQuotaRecord->consume_quota);            // 添加当月消费额（不包含冻结）
            $wallet->freeze_consume_quota = DB::raw('freeze_consume_quota - ' . $walletConsumeQuotaRecord->consume_quota);     // 减去当月冻结的消费额
            $wallet->save();
            // 4.更新消费额记录
            $walletConsumeQuotaRecord->status = WalletConsumeQuotaRecord::STATUS_UNFREEZE;
            $walletConsumeQuotaRecord->save();
        }

        // 3. 发送同步消费额到tps的队列
        ConsumeQuotaSyncToTpsJob::dispatch($order);
    }

    /**
     * 退回消费额
     * @param Order $order
     */
    public static function refundConsumeQuota(Order $order)
    {
        // todo
        // 判断消费额是否可退回
        // 1. 退回自己的消费额
        // 2. 退回上级的消费额
    }


    /**
     * 同步消费额数据到tps, 需要按订单去同步
     * @param Order $order
     * @return bool
     */
    public static function syncConsumeQuotaToTps(Order $order)
    {
        // 同步消费额到TPS
        $records = $order->consumeQuotaRecords()->whereIn('status', [
            WalletConsumeQuotaRecord::STATUS_UNFREEZE ,
            WalletConsumeQuotaRecord::STATUS_FAILED]
        )->get();
        // 拼装要发送的数据
        $data = [];
        $recordIds= [];
        foreach ($records as $record) {
            $tpsBind = TpsBindService::getTpsBindInfoByOriginInfo( $record->origin_id, $record->origin_type );
            if(empty($tpsBind)){
                // 用户没有绑定tps账号, 不再置换
                Log::warning('用户没有绑定tps账号, 不置换积分以及消费额', ['origin_id' => $record->origin_id, 'origin_type' => $record->origin_type, 'quotaRecord' => $record]);
                continue;
            }
            $data[] = [
                'orderId'       => $order->order_no,
                'orderPayTime'  => $order->pay_time,
                'createTime'    => $order->created_at->toDateTimeString(),
                'customerId'    => $tpsBind->tps_uid,
                'shopkeeperId'  => $tpsBind->tps_uid,
                'orderAmountUsd'=> $record->tps_consume_quota,
                'orderProfitUsd'=> $record->consume_quota_profit,
                'score'         => $record->sync_tps_credit,
                'status'        => $record->status,
            ];
            $recordIds[] = $record->id;
        }
        if( empty($data) )
        {
            return false;
        }
        // 发起请求
        $res = TpsApi::syncQuotaRecords( $data );
        $status = ( $res['code']=='101' ) ? WalletConsumeQuotaRecord::STATUS_FAILED : WalletConsumeQuotaRecord::STATUS_REPLACEMENT;

        $saveData = [
            'status' => $status,
            'sync_time' => Carbon::now(),
        ];
        if( !WalletConsumeQuotaRecord::whereIn('id', $recordIds)->update( $saveData ) )
        {
            Log::error('消费记录提交成功，但入库修改失败', [
                'WalletConsumeQuotaRecord ids' => $recordIds,
                'saveData' => $saveData
            ]);
        }
    }

    /**
     * 生成 消费额流水单号
     * @return string
     */
    public static function genConsumeQuotaNo()
    {
        $billNo = 'C'. date('Ymd') .substr(time(), -6, 6). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $billNo;
    }

    /**
     * @param $data
     * @return WalletConsumeQuotaRecord
     */
    private static function addConsumeQuotaRecord($data)
    {
        $wallet = $data['wallet'];
        /** @var Order $order */
        $order = $data['order'];
        $type = $data['type'];

        $consumeQuotaRecord = new WalletConsumeQuotaRecord();
        $consumeQuotaRecord->wallet_id = $wallet->id;
        $consumeQuotaRecord->consume_quota_no = self::genConsumeQuotaNo();
        $consumeQuotaRecord->origin_id = $wallet->origin_id;
        $consumeQuotaRecord->origin_type = $wallet->origin_type;
        $consumeQuotaRecord->type = $type;
        $consumeQuotaRecord->order_id = $order->id;
        $consumeQuotaRecord->order_no = $order->order_no;
        $consumeQuotaRecord->pay_price = $order->pay_price;
        $consumeQuotaRecord->order_profit_amount = $data['order_profit_amount'];

        if ($type == WalletConsumeQuotaRecord::TYPE_SELF) {
            $consumeQuotaRecord->consume_quota = $data['consume_quota'];
            $consumeQuotaRecord->consume_quota_profit = $data['consume_quota_profit'];
            $consumeQuotaRecord->tps_consume_quota = $data['tps_consume_quota'];
            $consumeQuotaRecord->tps_credit = $data['tps_credit'];
        }

        $consumeQuotaRecord->sync_tps_credit = $data['sync_tps_credit'];
        $consumeQuotaRecord->consume_user_mobile = $order->notify_mobile;
        $consumeQuotaRecord->status = WalletConsumeQuotaRecord::STATUS_FREEZE;
        $consumeQuotaRecord->save();
        return $consumeQuotaRecord;
    }

    /**
     * 创建 消费额解冻记录
     * @param WalletConsumeQuotaRecord $walletConsumeQuotaRecord
     * @param Wallet $wallet
     * @return WalletConsumeQuotaUnfreezeRecord
     */
    private static function addConsumeQuotaUnfreezeRecord(WalletConsumeQuotaRecord $walletConsumeQuotaRecord, Wallet $wallet)
    {
        $walletConsumeQuotaUnfreezeRecord = new WalletConsumeQuotaUnfreezeRecord();
        $walletConsumeQuotaUnfreezeRecord->wallet_id = $wallet->id;
        $walletConsumeQuotaUnfreezeRecord->origin_id = $walletConsumeQuotaRecord->origin_id;
        $walletConsumeQuotaUnfreezeRecord->origin_type = $walletConsumeQuotaRecord->origin_type;
        $walletConsumeQuotaUnfreezeRecord->unfreeze_consume_quota = $walletConsumeQuotaRecord->consume_quota;
        $walletConsumeQuotaUnfreezeRecord->save();

        return $walletConsumeQuotaUnfreezeRecord;
    }

    /**
     * 获取消费额记录
     * @param $param
     * @param int $pageSize
     * @param $withQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder
     */
    public static function getConsumeQuotaRecordList($param, $pageSize = 15, $withQuery = false)
    {
        $consumeQuotaNo = array_get($param, 'consumeQuotaNo', '');
        $startDate = array_get($param, 'startDate', '');
        $endDate = array_get($param, 'endDate', '');
        $status = array_get($param, 'status', 0);
        $originId = array_get($param, 'originId', 0);
        $originType = array_get($param, 'originType', 0);
        $type = array_get($param, 'type', '');
        $syncTpsCredit = array_get($param, 'syncTpsCredit', false);
        $tpsConsumeQuota = array_get($param, 'tpsConsumeQuota', false);

        $query = WalletConsumeQuotaRecord::query();
        if ($originId) {
            $query->where('origin_id', $originId);
        }
        if ($originType) {
            $query->where('origin_type', $originType);
        }
        if ($consumeQuotaNo) {
            $query->where('consume_quota_no', 'like', "%$consumeQuotaNo%");
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($syncTpsCredit) {
            $query->where('sync_tps_credit', '>', 0);
        }
        if ($tpsConsumeQuota) {
            $query->where('tps_consume_quota', '>', 0);
        }

        if ($startDate) $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
        if ($endDate) $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
        if($startDate && $endDate){
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }else if($startDate){
            $query->where('created_at', '>', $startDate);
        }else if($endDate){
            $query->where('created_at', '<', $endDate);
        }

        $query->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);

            return $data;
        }
    }

    /**
     * 根据id获取消费额记录
     * @param $id
     * @return WalletConsumeQuotaRecord
     */
    public static function getConsumeQuotaRecordById($id)
    {
        $consumeQuotaRecord = WalletConsumeQuotaRecord::find($id);
        return $consumeQuotaRecord;
    }

    /**
     * 获取消费额详情, 包含订单信息, 解冻信息等
     * @param $id
     * @return WalletConsumeQuotaRecord|WalletConsumeQuotaRecord[]|Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public static function getDetailById($id)
    {
        $consumeQuotaRecord = WalletConsumeQuotaRecord::with('order:id,pay_time')
            ->with('unfreezeRecord')
            ->find($id);
        return $consumeQuotaRecord;
    }

    /**
     * 根据id获取消费额解冻记录
     * @param $id
     * @return WalletConsumeQuotaUnfreezeRecord
     */
    public static function getConsumeQuotaUnfreezeRecordById($id)
    {
        $unfreezeRecord = WalletConsumeQuotaUnfreezeRecord::find($id);
        return $unfreezeRecord;
    }
}