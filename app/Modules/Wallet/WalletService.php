<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Bizer\Bizer;
use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchant;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Order\OrderRefund;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService extends BaseService
{

    /**
     * 创建钱包
     * @param User|Merchant|Oper|Bizer|CsMerchant $user
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
        } elseif ($user instanceof Bizer) {
            $originType = Wallet::ORIGIN_TYPE_BIZER;
        } elseif ($user instanceof CsMerchant) {
            $originType = Wallet::ORIGIN_TYPE_CS;
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
        return Wallet::find($wallet->id);
    }

    /**
     * @param Wallet $wallet
     * @param $amount
     * @param $type
     * @param int $objId
     * @return Wallet
     * @throws \Exception
     */
    public static function addBalance(Wallet $wallet, $amount, $type, $objId=0)
    {
        if($amount < 0){
            throw new BaseResponseException('增加的金额不能小于零');
        }

        // 1.添加冻结金额
        // 2.添加钱包流水
        DB::beginTransaction();
        try{
            $wallet->increment('balance', $amount);  // 更新钱包的冻结金额

            // 钱包流水表 添加钱包流水记录
            $walletBill = new WalletBill();
            $walletBill->wallet_id = $wallet->id;
            $walletBill->origin_id = $wallet->origin_id;
            $walletBill->origin_type = $wallet->origin_type;
            $walletBill->bill_no = WalletService::createWalletBillNo();
            $walletBill->type = $type;
            $walletBill->obj_id = $objId;
            $walletBill->inout_type = WalletBill::IN_TYPE;
            $walletBill->amount = $amount;
            $walletBill->amount_type = WalletBill::AMOUNT_TYPE_UNFREEZE;
            $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
            $walletBill->after_balance = $wallet->balance;
            $walletBill->save();

            DB::commit();
            return $wallet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('增加余额数据库错误', [
                'message' => $e->getMessage(),
                'data' => $e
            ]);
            throw $e;
        }

    }

    /**
     * 增加冻结金额
     * @param Wallet $wallet
     * @param $amount
     * @param int $type 交易类型
     * @param int $objId
     * @return Wallet
     */
    public static function addFreezeBalance(Wallet $wallet, $amount, $type, $objId = 0)
    {
        if($amount < 0){
            throw new BaseResponseException('增加的金额不能小于零');
        }

        // 1.添加冻结金额
        // 2.添加钱包流水
        $wallet->increment('freeze_balance', $amount);  // 更新钱包的冻结金额

        // 钱包流水表 添加钱包流水记录
        $walletBill = new WalletBill();
        $walletBill->wallet_id = $wallet->id;
        $walletBill->origin_id = $wallet->origin_id;
        $walletBill->origin_type = $wallet->origin_type;
        $walletBill->bill_no = WalletService::createWalletBillNo();
        $walletBill->type = $type;
        $walletBill->obj_id = $objId;
        $walletBill->inout_type = WalletBill::IN_TYPE;
        $walletBill->amount = $amount;
        $walletBill->amount_type = WalletBill::AMOUNT_TYPE_FREEZE;
        $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
        $walletBill->after_balance = $wallet->balance;
        $walletBill->save();
        return $wallet;
    }

    /**
     * 增加余额
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param Wallet $wallet
     */
    public static function addFreezeBalanceByFeeSplitting(FeeSplittingRecord $feeSplittingRecord, Wallet $wallet)
    {
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
        // 添加冻结金额
        self::addFreezeBalance($wallet, $feeSplittingRecord->amount, $type, $feeSplittingRecord->id);
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
        if ($wallet->freeze_balance - $feeSplittingRecord->amount < 0) {
            Log::info('钱包冻结金额小于要解冻的金额', [
                'wallet' => $wallet,
                'feeSplittingRecord' => $feeSplittingRecord,
            ]);
            throw new BaseResponseException('钱包冻结金额小于要解冻的金额');
        }
        $wallet->freeze_balance = DB::raw('freeze_balance - ' . $feeSplittingRecord->amount);
        $wallet->balance = DB::raw('balance + ' . $feeSplittingRecord->amount);
        $wallet->save();
        // 4.更新分润记录
        $feeSplittingRecord->status = FeeSplittingRecord::STATUS_UNFREEZE;
        $feeSplittingRecord->save();
    }

    /**
     * 分润退款
     * @param FeeSplittingRecord $feeSplittingRecord
     */
    public static function minusBalanceByFeeSplitting(FeeSplittingRecord $feeSplittingRecord)
    {
        // todo
        // 1. 判断状态, 若已解冻, 则不能退回
        // 2. 退回冻结金额
        // 3. 添加钱包流水
    }

    /**
     * 减少钱包余额
     * @param Wallet $wallet 钱包对象
     * @param number $amount 余额
     * @param int $type 交易类型
     * @param int $objId 交易来源ID
     * @return Wallet | WalletBill
     * @throws \Exception
     */
    public static function minusBalance(Wallet $wallet, $amount, $type, $objId = 0)
    {
        if($amount < 0){
            throw new BaseResponseException('减少的金额不能小于零');
        }

        if ($wallet->balance < $amount) {
            throw new BaseResponseException('钱包余额不足');
        }

        // 1.添加冻结金额
        // 2.添加钱包流水
        DB::beginTransaction();
        try{
            $wallet->decrement('balance', $amount);  // 更新钱包的冻结金额

            // 钱包流水表 添加钱包流水记录
            $walletBill = new WalletBill();
            $walletBill->wallet_id = $wallet->id;
            $walletBill->origin_id = $wallet->origin_id;
            $walletBill->origin_type = $wallet->origin_type;
            $walletBill->bill_no = WalletService::createWalletBillNo();
            $walletBill->type = $type;
            $walletBill->obj_id = $objId;
            $walletBill->inout_type = WalletBill::OUT_TYPE;
            $walletBill->amount = $amount;
            $walletBill->amount_type = WalletBill::AMOUNT_TYPE_UNFREEZE;
            $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
            $walletBill->after_balance = $wallet->balance;
            $walletBill->save();
            DB::commit();
            if($walletBill->type==WalletBill::TYPE_PLATFORM_SHOPPING){
                return $walletBill;
            }
            return $wallet;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('减余额数据库错误', [
                'message' => $e->getMessage(),
                'data' => $e
            ]);
            throw $e;
        }

    }

    /**
     * 生成 钱包流水单号
     * @return string
     */
    public static function createWalletBillNo()
    {
        $billNo = 'B'. date('Ymd') .substr(time(), -6, 6). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder
     */
    public static function getBillList($param, $pageSize = 15, $withQuery = false)
    {
        $billNo = array_get($param, 'billNo', '');
        $startDate = array_get($param, 'startDate', '');
        $endDate = array_get($param, 'endDate', '');
        $type = array_get($param, 'type');
        $originId = array_get($param, 'originId', 0);
        $originType = array_get($param, 'originType', 0);
        $walletId = array_get($param, 'walletId', 0);

        $query = WalletBill::query()
            ->where('amount','>',0);
        if ($originId) {
            $query->where('origin_id', $originId);
        }
        if ($originType) {
            $query->where('origin_type', $originType);
        }
        if ($billNo) {
            $query->where('bill_no', 'like', "%$billNo%");
        }
        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }
        if ($type) {
            if(is_array($type) || $type instanceof Collection){
                $query->whereIn('type', $type);
            }else {
                $query->where('type', $type);
            }
        }
        if ($startDate && $endDate) {
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            $query->where('created_at', '>', $startDate);
        } elseif ($endDate) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $query->where('created_at', '<', $endDate);
        }
        $query->orderBy('id', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function ($item) {
                $item->merchant_name = MerchantService::getNameById($item->origin_id);
                $item->oper_name = OperService::getNameById($item->origin_id);
                if ($item->origin_type == WalletBill::ORIGIN_TYPE_MERCHANT) {
                    $item->merchant_level = MerchantService::getById($item->origin_id)->level;
                }
                if ($item->origin_type == WalletBill::ORIGIN_TYPE_USER) {
                    $item->user_mobile = UserService::getUserById($item->origin_id)->mobile;
                    // 如果是下级消费返利 返回下级的手机号码
                    if (in_array($item->type, [WalletBill::TYPE_SUBORDINATE, WalletBill::TYPE_SUBORDINATE_REFUND])) {
                        $feeSplitting = FeeSplittingService::getFeeSplittingRecordById($item->obj_id);
                        $order = OrderService::getById($feeSplitting->order_id);
                        $item->user_mobile = $order->notify_mobile;
                    }
                }
                if ($item->origin_type == WalletBill::ORIGIN_TYPE_BIZER) {
                    $bizer = BizerService::getById($item->origin_id);
                    $item->bizer_mobile = $bizer->mobile;
                    $item->bizer_name = $bizer->name;
                }
                if (in_array($item->type, [WalletBill::TYPE_WITHDRAW, WalletBill::TYPE_WITHDRAW_FAILED])) {
                    $walletWithdraw = WalletWithdrawService::getWalletWithdrawById($item->obj_id);
                    $item->status = $walletWithdraw->status;
                } else {
                    $item->status = 0;
                }
                if($item->type==WalletBill::TYPE_PLATFORM_SHOPPING ){
                    // 如果使用钱包平台消费
                    $order = OrderService::getById($item->obj_id);
                    $item->order = $order;
                }
                if($item->type==WalletBill::TYPE_PLATFORM_REFUND){
                    // 如果使用钱包平台退款
                    $refundOrder = OrderRefund::find($item->obj_id);
                    $order = OrderService::getInfoByOrderNo($refundOrder->order_no);
                    $item->order = $order;
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

            //返利金额
            $bill->profitAmount = $feeSplittingRecords->amount;
            $bill->order = OrderService::getById($feeSplittingRecords->order_id, ['id', 'order_no', 'status', 'created_at', 'pay_time', 'notify_mobile', 'pay_price']);

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

    /**
     * 根据id 获取钱包信息
     * @param $id
     * @param array $filed
     * @return Wallet
     */
    public static function getWalletById($id, $filed = ['*'])
    {
        $wallet = Wallet::find($id, $filed);
        return $wallet;
    }

    /**
     * 获取钱包列表
     * @param $params
     * @param int $pageSize
     * @param bool $withQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder
     */
    public static function getWalletList($params, $pageSize = 15, $withQuery = false)
    {
        $originType = array_get($params, 'originType');
        $originId = array_get($params, 'originId');
        $status = array_get($params, 'status');
        $userMobile = array_get($params, 'userMobile');
        $bizerMobile = array_get($params, 'bizerMobile', '');
        $merchantName = array_get($params, 'merchantName');
        $operName = array_get($params, 'operName');
        $bizerName = array_get($params, 'bizerName', '');

        $query = Wallet::query();

        if($originType){
            $query->where('origin_type', $originType);
        }
        if($originId){
            $query->where('origin_id', $originId);
        }
        if($originType == Wallet::ORIGIN_TYPE_USER && $userMobile){
            $originIds = UserService::getUserColumnArrayByMobile($userMobile, 'id');
        }
        if($originType == Wallet::ORIGIN_TYPE_MERCHANT){
            if ($merchantName && $operName) {
                $operIds = OperService::getOperColumnArrayByOperName($operName, 'id');
                $originIds = MerchantService::getMerchantColumnArrayByParams(compact('operIds', 'merchantName'), 'id');
            } elseif ($merchantName) {
                $originIds = MerchantService::getMerchantColumnArrayByParams(compact('merchantName'), 'id');
            } elseif ($operName) {
                $operIds = OperService::getOperColumnArrayByOperName($operName, 'id');
                $originIds = MerchantService::getMerchantColumnArrayByParams(compact('operIds'), 'id');
            }
        }
        if ($originType == Wallet::ORIGIN_TYPE_OPER && $operName) {
            $originIds = OperService::getOperColumnArrayByOperName($operName, 'id');
        }
        if ($originType == Wallet::ORIGIN_TYPE_BIZER && ($bizerName || $bizerMobile)) {
            $originIds = BizerService::getBizerColumnArrayByParams(compact('bizerMobile', 'bizerName'), 'id');
        }
        if(isset($originIds)){
            $query->whereIn('origin_id', $originIds);
        }
        if($status){
            if(is_array($status) || $status instanceof Collection){
                $query->whereIn('status', $status);
            }else {
                $query->where('status', $status);
            }
        }
        $query->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function($item) {
                if ($item->origin_type == Wallet::ORIGIN_TYPE_USER) {
                    $user = UserService::getUserById($item->origin_id);
                    $bankCard = self::getBankCardByOriginInfo($item->origin_id, $item->origin_type);
                    $item->user_mobile = $user->mobile ?? '';
                    $item->bank_open_name = $bankCard->bank_card_open_name??'';
                    $item->bank_card_no = $bankCard->bank_card_no?? '';
                    $item->bank_name = $bankCard->bank_name?? '';
                    $item->sub_bank_name = $bankCard->sub_bank_name?? '';
                    $item->bank_card_type = $bankCard->bank_card_type?? '';
                } elseif ($item->origin_type == Wallet::ORIGIN_TYPE_MERCHANT) {
                    $merchant = MerchantService::getById($item->origin_id);
                    $item->merchant_name = $merchant->name ?? '';
                    $item->oper_name = OperService::getNameById($merchant->oper_id ?? '');
                    $item->bank_open_name = $merchant->bank_open_name ?? '';
                    $item->bank_card_no = $merchant->bank_card_no ?? '';
                    $item->sub_bank_name = $merchant->sub_bank_name ?? '';
                    $item->bank_card_type = $merchant->bank_card_type ?? '';
                } elseif ($item->origin_type == Wallet::ORIGIN_TYPE_OPER) {
                    $oper = OperService::getById($item->origin_id);
                    $item->oper_name = $oper->name;
                    $item->bank_open_name = $oper->bank_open_name;
                    $item->bank_card_no = $oper->bank_card_no;
                    $item->sub_bank_name = $oper->sub_bank_name;
                } elseif ($item->origin_type == Wallet::ORIGIN_TYPE_BIZER) {
                    $bizer = BizerService::getById($item->origin_id);
                    $item->bizer_name = !empty($bizer) ? $bizer->name : '';
                    $item->bizer_mobile = !empty($bizer) ? $bizer->mobile : '';
                    $bizerBank = BankCardService::getBankCardByOriginInfo($item->origin_id, $item->origin_type);
                    $item->bank_open_name = $bizerBank->bank_card_open_name ?? '';
                    $item->bank_card_no = $bizerBank->bank_card_no ?? '';
                    $item->bank_name = $bizerBank->bank_name ?? '';
                    $item->bank_card_type = $bizerBank->bank_card_type ?? '';
                }
            });
            return $data;
        }
    }

    /**
     * 获取银行卡信息
     * @param $originId
     * @param $originType
     * @return BankCard
     */
    public static function getBankCardByOriginInfo($originId, $originType)
    {
        $bankCard = BankCard::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        return $bankCard;
    }

    /**
     * 更改钱包状态
     * @param $id
     * @return Wallet
     */
    public static function changeWalletStatus($id)
    {
        $wallet = self::getWalletById($id);
        $wallet->status = $wallet->status == Wallet::STATUS_ON ? Wallet::STATUS_OFF : Wallet::STATUS_ON;
        $wallet->save();
        return $wallet;
    }

    /**
     * 通过分润记录id 获取解冻分润记录
     * @param $feeSplittingRecordId
     * @return WalletBalanceUnfreezeRecord
     */
    public static function getBalanceUnfreezeRecordByFeeSplittingId($feeSplittingRecordId)
    {
        $balanceUnfreezeRecord = WalletBalanceUnfreezeRecord::where('fee_splitting_record_id', $feeSplittingRecordId)
            ->first();
        return $balanceUnfreezeRecord;
    }

    /**
     * 校验旧密码
     * Author：  Jerry
     * Date：    180901
     * @param   string  $password
     * @param   integer $userId
     */
    public static function checkPayPassword($password, $userId )
    {
        $wallet = WalletService::getWalletInfoByOriginInfo($userId, Wallet::ORIGIN_TYPE_USER);
        // 通过新提交的明文密码生成密文密码
        $putPassword = Wallet::genPassword(  $password, $wallet['salt']);
        if( $putPassword != $wallet['withdraw_password'] )
        {
            throw new BaseResponseException('交易密码不正确', ResultCode::PARAMS_INVALID);
        }
        return $wallet;
    }

    /**
     * 更新钱包和钱包流水
     * @param Wallet $wallet
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param FeeSplittingRecord $newFeeSplittingRecord
     * @return WalletBill
     */
    public static function updateWalletAndWalletBill(Wallet $wallet, FeeSplittingRecord $feeSplittingRecord, FeeSplittingRecord $newFeeSplittingRecord)
    {
        $wallet->decrement('balance', $feeSplittingRecord->amount);
        $wallet->increment('balance', $newFeeSplittingRecord->amount);

        $walletBill = WalletBill::where('origin_id', $feeSplittingRecord->origin_id)
            ->where('origin_type', $feeSplittingRecord->origin_type)
            ->where('obj_id', $feeSplittingRecord->id)
            ->first();
        if (empty($walletBill)) throw new BaseResponseException('分润流水不存在');
        $walletBill->amount = $newFeeSplittingRecord->amount;
        $walletBill->after_amount = $walletBill->after_amount - $feeSplittingRecord->amount + $newFeeSplittingRecord->amount;
        $walletBill->after_balance = $walletBill->after_balance - $feeSplittingRecord->amount + $newFeeSplittingRecord->amount;
        $walletBill->save();

        return $walletBill;
    }
}