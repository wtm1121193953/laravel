<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\User\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        // 1. 添加上级的消费额 因为上级要使用用户添加消费额之前的累计消费额
        self::addFreezeConsumeQuotaToParent($order);
        // 2. 添加自己的消费额
        self::addFreezeConsumeQuotaToSelf($order);
    }

    /**
     * 添加自己的消费额
     * @param Order $order
     * @throws \Exception
     */
    public static function addFreezeConsumeQuotaToSelf(Order $order)
    {
        // 1. 获取用户自己的钱包
        $user = UserService::getUserById($order->user_id);
        $wallet = WalletService::getWalletInfo($user);
        DB::beginTransaction();
        try {
            // 2. 获取用户自己的消费额
            $consumeQuota = $order->pay_price;
            $wallet->freeze_consume_quota += $consumeQuota;  // 当月冻结
            $wallet->total_consume_quota += $consumeQuota;   // 个人累计
            $wallet->save();
            // 3. 添加消费额记录
            self::createWalletConsumeQuotaRecord($order, $wallet, WalletConsumeQuotaRecord::TYPE_SELF, $consumeQuota);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 添加上级的消费额
     * @param Order $order
     */
    public static function addFreezeConsumeQuotaToParent(Order $order)
    {
        // 1. 找到用户和用户的上级
        $user = UserService::getUserById($order->user_id);
        $parent = InviteUserService::getParent($order->user_id);
        if ($parent == null) {
            return;
        }
        // 2. 获取用户 和 用户上级 的 钱包
        $userWallet = WalletService::getWalletInfo($user);
        $parentWallet = WalletService::getWalletInfo($parent);
        // 3. 计算返给上级的消费额
        $hundred = floor((($userWallet->total_consume_quota % 100) + $order->pay_price) / 100);
        if($hundred > 0){
            // 只有当前消费额与累计消费额累加后超过一百, 才给上级反消费额
            $consumeQuota = $hundred * 50;
            // 4. 添加当月冻结中的消费额 和 累计分享消费额
            $parentWallet->freeze_consume_quota += $consumeQuota;         // 上级添加冻结消费额
            $parentWallet->total_share_consume_quota += $consumeQuota;    // 上级添加 下级消费返的累计消费额
            $parentWallet->save();
            // 5. 添加消费额记录
            self::createWalletConsumeQuotaRecord($order, $parentWallet, WalletConsumeQuotaRecord::TYPE_SUBORDINATE, $consumeQuota);
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

        // 3. 发送同步消费额到tps的队列

        $walletConsumeQuotaRecords = WalletConsumeQuotaRecord::where('order_id', $order->id)->get();
        foreach ($walletConsumeQuotaRecords as $walletConsumeQuotaRecord) {
            // 1.找到钱包
            $wallet = WalletService::getWalletInfoByOriginInfo($walletConsumeQuotaRecord->origin_id, $walletConsumeQuotaRecord->origin_type);
            // 2.添加消费额解冻记录
            self::createWalletConsumeQuotaUnfreezeRecord($walletConsumeQuotaRecord, $wallet);
            // 3.更新钱包信息
            $wallet->consume_quota += $walletConsumeQuotaRecord->consume_quota;            // 添加当月消费额（不包含冻结）
            $wallet->freeze_consume_quota -= $walletConsumeQuotaRecord->consume_quota;     // 减去当月冻结的消费额
            $wallet->save();
            // 4.更新消费额记录
            $walletConsumeQuotaRecord->status = WalletConsumeQuotaRecord::STATUS_UNFREEZE;
            $walletConsumeQuotaRecord->save();
        }
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
     */
    public static function syncConsumeQuotaToTps(Order $order)
    {
        // todo
        //
    }

    /**
     * 生成 消费额流水单号
     * @return string
     */
    public static function createConsumeQuotaNo()
    {
        $billNo = date('Ymd') .substr(time(), -7, 7). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $billNo;
    }

    /**
     * 创建消费额记录
     * @param Order $order
     * @param Wallet $wallet
     * @param $type
     * @param $consumeQuota
     * @return WalletConsumeQuotaRecord
     */
    private static function createWalletConsumeQuotaRecord(Order $order, Wallet $wallet, $type, $consumeQuota)
    {
        $totalFeeSplittingAmount = FeeSplittingService::getOrderFeeSplittingAmountByOrderId($order->id);

        $consumeQuotaRecord = new WalletConsumeQuotaRecord();
        $consumeQuotaRecord->wallet_id = $wallet->id;
        $consumeQuotaRecord->consume_quota_no = self::createConsumeQuotaNo();
        $consumeQuotaRecord->origin_id = $wallet->origin_id;
        $consumeQuotaRecord->origin_type = $wallet->origin_type;
        $consumeQuotaRecord->type = $type;
        $consumeQuotaRecord->order_id = $order->id;
        $consumeQuotaRecord->order_no = $order->order_no;
        $consumeQuotaRecord->pay_price = $order->pay_price;
        $consumeQuotaRecord->order_profit_amount = OrderService::getProfitAmount($order);           // 订单利润
        $consumeQuotaRecord->consume_quota = $consumeQuota;                                         // 消费额
        $consumeQuotaRecord->consume_quota_profit = $consumeQuotaRecord->order_profit_amount - $totalFeeSplittingAmount;   // 消费额利润 = 订单利润 - 总分润总金额
        $consumeQuotaRecord->tps_credit = $consumeQuota / 6 / 6.5;                                  // 消费额转化的tps积分值
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
    private static function createWalletConsumeQuotaUnfreezeRecord(WalletConsumeQuotaRecord $walletConsumeQuotaRecord, Wallet $wallet)
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
     * @return WalletConsumeQuotaRecord|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getConsumeQuotaRecordList($param, $pageSize = 15, $withQuery = false)
    {
        $consumeQuotaNo = array_get($param, 'consumeQuotaNo', '');
        $startDate = array_get($param, 'startDate', '');
        $endDate = array_get($param, 'endDate', '');
        $status = array_get($param, 'status', 0);
        $originId = array_get($param, 'originId', 0);
        $originType = array_get($param, 'originType', 0);

        $query = WalletConsumeQuotaRecord::when($originId, function (Builder $query) use ($originId) {
            $query->where('origin_id', $originId);
        })
            ->when($originType, function (Builder $query) use ($originType) {
                $query->where('origin_type', $originType);
            })
            ->when($consumeQuotaNo, function (Builder $query) use ($consumeQuotaNo) {
                $query->where('consume_quota_no', $consumeQuotaNo);
            })
            ->when($startDate, function (Builder $query) use ($startDate) {
                $query->whereDate('created_at', '>', $startDate);
            })
            ->when($endDate, function (Builder $query) use ($endDate) {
                $query->whereDate('created_at', '<', $endDate);
            })
            ->when($status, function (Builder $query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc');
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