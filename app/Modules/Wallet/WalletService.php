<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Builder;

class WalletService extends BaseService
{

    /**
     * 创建钱包
     * @param User|Merchant|Oper $user
     * @return Wallet
     */
    public static function getWalletInfo($user)
    {
        if ($user instanceof User) {
            $originType = Wallet::ORIGIN_TYPE_USER;
        } elseif ($user instanceof Merchant) {
            $originType = Wallet::ORIGIN_TYPE_MERCHANT;
        } elseif ($user instanceof Oper) {
            $originType = Wallet::ORIGIN_TYPE_OPER;
        } else {
            throw new ParamInvalidException('参数错误');
        }

        $wallet = Wallet::where('origin_id', $user->id)
            ->where('origin_type', $originType)
            ->first();
        if (empty($wallet)) {
            $wallet = self::createWallet($user->id, $originType);
        }
        return $wallet;
    }

    /**
     * 创建钱包
     * @param $originId
     * @param $originType
     * @return Wallet
     */
    private static function createWallet($originId, $originType)
    {
        $wallet = new Wallet();
        $wallet->origin_id = $originId;
        $wallet->origin_type = $originType;
        $wallet->save();
        return $wallet;
    }

    /**
     * 增加余额
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param Wallet $wallet
     */
    public static function addFreezeBalance(FeeSplittingRecord $feeSplittingRecord, Wallet $wallet)
    {
        // 1.添加冻结金额
        // 2.添加钱包流水
        $wallet->freeze_balance = $wallet->freeze_balance + $feeSplittingRecord->amount;  // 更新钱包的冻结金额
        $wallet->save();

        // 钱包流水表 添加钱包流水记录
        if ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_SELF) {
            $type = WalletBill::TYPE_SELF;
        } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_PARENT) {
            $type = WalletBill::TYPE_SUBORDINATE;
        } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_OPER) {
            $type = WalletBill::TYPE_OPER;
        } else {
            throw new ParamInvalidException('分润类型参数错误');
        }
        $walletBill = new WalletBill();
        $walletBill->wallet_id = $wallet->id;
        $walletBill->origin_id = $wallet->origin_id;
        $walletBill->origin_type = $wallet->origin_type;
        $walletBill->bill_no = WalletService::createWalletBillNo();
        $walletBill->type = $type;
        $walletBill->obj_id = $feeSplittingRecord->id;
        $walletBill->inout_type = WalletBill::IN_TYPE;
        $walletBill->amount = $feeSplittingRecord->amount;
        $walletBill->amount_type = WalletBill::AMOUNT_TYPE_FREEZE;
        $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
        $walletBill->save();
    }

    /**
     * 解冻金额
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function unfreezeBalance(FeeSplittingRecord $feeSplittingRecord)
    {
        // 1.找到每条记录对应的钱包
        $wallet = self::getWalletInfoByOriginInfo($feeSplittingRecord->origin_id, $feeSplittingRecord->origin_type);
        // 2.添加钱包金额解冻记录
        self::createWalletBalanceUnfreezeRecord($feeSplittingRecord, $wallet);
        // 3.更新钱包
        $wallet->freeze_balance = $wallet->freeze_balance - $feeSplittingRecord->amount;
        $wallet->balance = $wallet->balance + $feeSplittingRecord->amount;
        $wallet->save();
        // 4.更新分润记录
        $feeSplittingRecord->status = FeeSplittingRecord::STATUS_UNFREEZE;
        $feeSplittingRecord->save();
    }

    /**
     * 分润退款
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function refundBalance(FeeSplittingRecord $feeSplittingRecord)
    {
        // todo
        // 1. 判断状态, 若已解冻, 则不能退回
        // 2. 退回冻结金额
        // 3. 添加钱包流水
    }

    /**
     * 生成 钱包流水单号
     * @return string
     */
    public static function createWalletBillNo()
    {
        $billNo = date('Ymd') .substr(time(), -7, 7). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $billNo;
    }

    /**
     * 根据用户ID和类型 获取 钱包信息
     * @param $originId
     * @param $originType
     * @return Wallet
     */
    public static function getWalletInfoByOriginInfo($originId, $originType)
    {
        $wallet = Wallet::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        if (empty($wallet)) {
            $wallet = self::createWallet($originId, $originType);
        }
        return $wallet;
    }

    /**
     * 创建钱包金额解冻记录
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param Wallet $wallet
     * @return WalletBalanceUnfreezeRecord
     */
    public static function createWalletBalanceUnfreezeRecord(FeeSplittingRecord $feeSplittingRecord, Wallet $wallet)
    {
        $walletBalanceUnfreezeRecord = new WalletBalanceUnfreezeRecord();
        $walletBalanceUnfreezeRecord->wallet_id = $wallet->id;
        $walletBalanceUnfreezeRecord->origin_id = $feeSplittingRecord->origin_id;
        $walletBalanceUnfreezeRecord->origin_type = $feeSplittingRecord->origin_type;
        $walletBalanceUnfreezeRecord->fee_splitting_record_id = $feeSplittingRecord->id;
        $walletBalanceUnfreezeRecord->unfreeze_amount = $feeSplittingRecord->amount;
        $walletBalanceUnfreezeRecord->save();

        return $walletBalanceUnfreezeRecord;
    }

    /**
     * 根据用户信息获取钱包流水
     * @param $param
     * @param int $pageSize
     * @param bool $withQuery
     * @return WalletBill|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getBillList($param, $pageSize = 15, $withQuery = false)
    {
        $billNo = array_get($param, 'billNo', '');
        $startDate = array_get($param, 'startDate', '');
        $endDate = array_get($param, 'endDate', '');
        $typeArr = array_get($param, 'typeArr', []);
        $originId = array_get($param, 'originId', 0);
        $originType = array_get($param, 'originType', 0);

        $query = WalletBill::when($originId, function (Builder $query) use ($originId) {
                $query->where('origin_id', $originId);
            })
            ->when($originType, function (Builder $query) use ($originType) {
                $query->where('origin_type', $originType);
            })
            ->when($billNo, function (Builder $query) use ($billNo) {
                $query->where('bill_no', $billNo);
            })
            ->when($startDate, function (Builder $query) use ($startDate) {
                $query->whereDate('created_at', '>', $startDate);
            })
            ->when($endDate, function (Builder $query) use ($endDate) {
                $query->whereDate('created_at', '<', $endDate);
            })
            ->when($typeArr, function (Builder $query) use ($typeArr) {
                $query->whereIn('type', $typeArr);
            })
            ->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function ($item) {
                $item->merchant_name = MerchantService::getNameById($item->origin_id);
                if (in_array($item->type, [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED])) {
                    $walletWithdraw = WalletWithdrawService::getWalletWithdrawById($item->obj_id);
                    $item->status = $walletWithdraw->status;
                } else {
                    $item->status = 0;
                }
            });

            return $data;
        }
    }

    /**
     * 通过ID获取钱包流水
     * @param $id
     * @return WalletBill
     */
    public static function getBillById($id)
    {
        $walletBill = WalletBill::find($id);
        return $walletBill;
    }

    /**
     * 获取账单详情, 包含账单的来源信息
     * @param $id
     * @return WalletBill|null
     */
    public static function getBillDetailById($id)
    {
        $bill = self::getBillById($id);
        if(empty($bill)){
            return null;
        }
        // 如果是返利相关, 补充返利的订单信息
        if(in_array($bill->type, [
            WalletBill::TYPE_SELF,
            WalletBill::TYPE_SUBORDINATE,
            WalletBill::TYPE_OPER,
            WalletBill::TYPE_SELF_CONSUME_REFUND,
            WalletBill::TYPE_SUBORDINATE_REFUND,
            WalletBill::TYPE_OPER_REFUND,
        ])){
            $feeSplittingRecords = FeeSplittingService::getFeeSplittingRecordById($bill->obj_id);
            $bill->order = OrderService::getById($feeSplittingRecords->order_id, ['id', 'order_no', 'status', 'created_at', 'pay_time']);

            // 如果是退款相关, 补充退款信息
            if(in_array($bill->type, [
                WalletBill::TYPE_SELF_CONSUME_REFUND,
                WalletBill::TYPE_SUBORDINATE_REFUND,
                WalletBill::TYPE_OPER_REFUND,
            ])){
                $bill->refund = OrderService::getRefundById($bill->order->id, ['id', 'refund_no', 'status', 'created_at']);
            }
        }
        // 提现相关, 补充提现单号
        if(in_array($bill->type, [
            WalletBill::TYPE_WITHDRAW,
            WalletBill::TYPE_WITHDRAW_FAILED,
        ])){
            $bill->withdraw = WalletWithdrawService::getWalletWithdrawById($bill->obj_id);
        }
        return $bill;
    }

    /**
     * 更新钱包提现密码
     * @param Wallet $wallet
     * @param $password
     * @return Wallet
     */
    public static function updateWalletWithdrawPassword(Wallet $wallet, $password)
    {
        $salt = str_random();
        $wallet->salt = $salt;
        $wallet->withdraw_password = Wallet::genPassword($password, $salt);
        $wallet->save();
        return $wallet;
    }
}