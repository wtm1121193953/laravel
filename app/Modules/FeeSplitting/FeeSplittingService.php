<?php

namespace App\Modules\FeeSplitting;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Bizer\Bizer;
use App\Modules\Bizer\BizerService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class FeeSplittingService extends BaseService
{

    /**
     * 根据订单执行分润
     * @param Order $order
     * @throws \Exception
     */
    public static function feeSplittingByOrder(Order $order)
    {
        // 获取订单利润
        $profitAmount = OrderService::getProfitAmount($order);
        DB::beginTransaction();
        try {
            $oper = OperService::getById($order->oper_id);
            // 只有切换到平台并且平台参与分成的运营中心才执行返利
            if ($oper->pay_to_platform == Oper::PAY_TO_PLATFORM_WITH_SPLITTING) {
                // 1 分给自己 5%
                self::feeSplittingToSelf($order, $profitAmount);
                // 2 分给上级 25%
                self::feeSplittingToParent($order, $profitAmount);
            }
            // 只有切换到平台的才给运营中心分润
            if ($oper->pay_to_platform != Oper::PAY_TO_OPER) {
                // 3 分给运营中心  50% || 100%
                self::feeSplittingToOper($order, $profitAmount);
            }
            // 4 修改订单中的分润状态
            OrderService::updateSplittingStatus($order);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw  $e;
        }
    }

    /**
     * 自返逻辑
     * @param Order $order
     * @param float $profitAmount
     * @throws \Exception
     */
    private static function feeSplittingToSelf(Order $order, float $profitAmount)
    {
        // 分润记录表 添加分润记录
        $originInfo = UserService::getUserById($order->user_id);

        DB::beginTransaction();
        try {
            $feeSplittingRecord = self::createFeeSplittingRecord($order, $originInfo, FeeSplittingRecord::TYPE_TO_SELF, $profitAmount);

            // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
            $wallet = WalletService::getWalletInfo($originInfo);

            WalletService::addFreezeBalance($feeSplittingRecord, $wallet);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 返利给上级逻辑
     * @param Order $order
     * @param float $profitAmount
     * @throws \Exception
     */
    private static function feeSplittingToParent(Order $order, float $profitAmount)
    {
        $parent = InviteUserService::getParent($order->user_id);
        if ($parent == null) {
            return;
        }
        DB::beginTransaction();
        try {
            $feeSplittingRecord = self::createFeeSplittingRecord($order, $parent, FeeSplittingRecord::TYPE_TO_PARENT, $profitAmount);

            // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
            $wallet = WalletService::getWalletInfo($parent);

            WalletService::addFreezeBalance($feeSplittingRecord, $wallet);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 返利给运营中心 和 业务员
     * @param Order $order
     * @param $profitAmount
     * @throws \Exception
     */
    private static function feeSplittingToOper(Order $order, $profitAmount)
    {
        // 通过订单中的merchant_id查找该商户的运营中心（准确点）
        $merchant = MerchantService::getById($order->merchant_id);
        $oper = OperService::getById($merchant->oper_id);
        if (empty($oper)) {
            return;
        }
        if ($oper->pay_to_platform == Oper::PAY_TO_OPER) {
            return;
        }

        $operFeeRatio = $operFeeRatioInit = UserCreditSettingService::getFeeSplittingRatioToOper($oper);
        $bizerFeeRatio = 0; // 业务员分润比例
        if ($operFeeRatioInit == null || $operFeeRatioInit <= 0) {
            return;
        }
        $bizer = BizerService::getById($order->bizer_id);
        if (!empty($bizer)) {
            if (!$order->bizer_divide) {
                $param = [
                    'operId' => $oper->id,
                    'bizerId' => $bizer->id,
                ];
                $operBizer = OperBizerService::getOperBizerByParam($param);
                $order->bizer_divide = $operBizer->divide;
                $order->save();
            }
            if ($operBizer->status == OperBizer::STATUS_SIGNED) {
                $bizerFeeRatio = $operFeeRatioInit * $order->bizer_divide / 100;
                $operFeeRatio = $operFeeRatioInit - $bizerFeeRatio;
                if ($operFeeRatio < 0) {
                    return;
                }
            }
        }

        DB::beginTransaction();
        try {
            if ($merchant->bizer_id && $bizerFeeRatio > 0) {
                // 计算业务员
                // 如果该商户有业务员，则给业务员分润
                self::feeSplittingToBizer($order, $profitAmount, $bizer, $bizerFeeRatio);
            }

            // 计算运营中心
            $feeSplittingRecord = self::createFeeSplittingRecord($order, $oper, FeeSplittingRecord::TYPE_TO_OPER, $profitAmount, $operFeeRatio);

            // 钱包表 首先查找是否有钱包，没有则新建钱包; 有钱包则更新钱包（的冻结金额）
            $wallet = WalletService::getWalletInfo($oper);
            if (!empty($feeSplittingRecord)) {
                WalletService::addFreezeBalance($feeSplittingRecord, $wallet);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 给业务员分润
     * @param Order $order
     * @param $profitAmount
     * @param Bizer $bizer
     * @param float $bizerFeeRatio
     * @return null|void
     * @throws \Exception
     */
    private static function feeSplittingToBizer(Order $order, $profitAmount, Bizer $bizer, float $bizerFeeRatio)
    {
        DB::beginTransaction();
        try {
            $feeSplittingRecord = self::createFeeSplittingRecord($order, $bizer, FeeSplittingRecord::TYPE_TO_BIZER, $profitAmount, $bizerFeeRatio);

            // 更新钱包 并 添加钱包流水
            $wallet = WalletService::getWalletInfo($bizer);
            if (!empty($feeSplittingRecord)) {
                WalletService::addFreezeBalance($feeSplittingRecord, $wallet);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 根据订单解冻分润金额
     * @param Order $order
     * @throws \Exception
     */
    public static function unfreezeSplittingByOrder(Order $order)
    {
        DB::beginTransaction();
        try {
            $feeSplittingRecords = FeeSplittingRecord::where('order_id', $order->id)->get();
            foreach ($feeSplittingRecords as $feeSplittingRecord) {
                // 如果不是冻结状态 则退出
                if ($feeSplittingRecord->status != FeeSplittingRecord::STATUS_FREEZE) continue;

                WalletService::unfreezeBalance($feeSplittingRecord);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 根据订单退回分润金额
     * @param $order
     */
    public static function refundSplittingByOrder($order)
    {
        // todo
        // 判断如果已解冻, 则不能退回
    }

    /**
     * 创建分润记录
     * @param Order $order
     * @param User|Merchant|Oper|Bizer $originInfo
     * @param $type
     * @param float $profitAmount
     * @param float $feeRatio
     * @return FeeSplittingRecord
     */
    private static function createFeeSplittingRecord(Order $order, $originInfo, $type, float $profitAmount, float $feeRatio = 0)
    {
        $merchantLevel = 0;
        if ($originInfo instanceof User) {
            $originType = FeeSplittingRecord::ORIGIN_TYPE_USER;
        } else if ($originInfo instanceof Merchant) {
            $originType = FeeSplittingRecord::ORIGIN_TYPE_MERCHANT;
        } else if ($originInfo instanceof Oper) {
            $originType = FeeSplittingRecord::ORIGIN_TYPE_OPER;
        }else if($originInfo instanceof Bizer){
            $originType = FeeSplittingRecord::ORIGIN_TYPE_BIZER;
        }else {
            throw new BaseResponseException('用户类型错误');
        }
        if ($type == FeeSplittingRecord::TYPE_TO_SELF) {
            // 1 自反比例
            $feeRatio = UserCreditSettingService::getFeeSplittingRatioToSelfSetting(); // 自反的分润比例
        } elseif ($type == FeeSplittingRecord::TYPE_TO_PARENT) {
            // 2 返上级比例
            if ($originType == FeeSplittingRecord::ORIGIN_TYPE_USER) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfUserSetting();
            } else if ($originType == FeeSplittingRecord::ORIGIN_TYPE_MERCHANT) {
                $merchantLevel = $originInfo->level;
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfMerchantSetting($merchantLevel);
            } else if ($originType == FeeSplittingRecord::ORIGIN_TYPE_OPER) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfOperSetting();
            } else {
                throw new BaseResponseException();
            }
        } elseif ($type == FeeSplittingRecord::TYPE_TO_OPER) {
            // 3 运营中心分润比例
        } elseif ($type == FeeSplittingRecord::TYPE_TO_BIZER) {
            // 4 业务员分润比例
        }
        else {
            throw new ParamInvalidException('分润类型错误');
        }

        $feeSplittingRecord = new FeeSplittingRecord();
        $feeSplittingRecord->origin_id = $originInfo->id;
        $feeSplittingRecord->origin_type = $originType;
        $feeSplittingRecord->merchant_level = $merchantLevel ?: 0;
        $feeSplittingRecord->order_id = $order->id;
        $feeSplittingRecord->order_no = $order->order_no;
        $feeSplittingRecord->order_profit_amount = $profitAmount;
        $feeSplittingRecord->ratio = $feeRatio;
        $feeSplittingRecord->amount = floor($profitAmount * $feeRatio) / 100;
        $feeSplittingRecord->type = $type;
        $feeSplittingRecord->status = FeeSplittingRecord::STATUS_FREEZE;
        $feeSplittingRecord->save();
        return $feeSplittingRecord;
    }

    /**
     * 通过id获取分润记录
     * @param $id
     * @return FeeSplittingRecord
     */
    public static function getFeeSplittingRecordById($id)
    {
        $feeSplittingRecord = FeeSplittingRecord::find($id);
        return $feeSplittingRecord;
    }

    /**
     * 通过 订单id 分润类型type 获取分润记录
     * @param $orderId
     * @param $type
     * @return FeeSplittingRecord
     */
    public static function getFeeSplittingRecordByOrderId($orderId,$type)
    {
        $feeSplittingRecord = FeeSplittingRecord::where('order_id', $orderId)->where('type',$type)->first();
        return $feeSplittingRecord;
    }

    /**
     * 通过订单id 获取 该订单的 总分润金额
     * @param $orderId
     * @return mixed
     */
    public static function getOrderFeeSplittingAmountByOrderId($orderId)
    {
        $amount = FeeSplittingRecord::where('order_id', $orderId)
            ->sum('amount');
        return $amount;
    }

    /**
     * 获取用户自返的返利记录
     * @param int $orderId 订单ID
     * @return FeeSplittingRecord
     */
    public static function getToSelfFeeSplittingRecordByOrderId($orderId)
    {
        $record = FeeSplittingRecord::where('order_id', $orderId)
            ->where('type', FeeSplittingRecord::TYPE_TO_SELF)
            ->first();
        return $record;
    }

    /**
     * 通过参数 获取 分润记录详情
     * @param $params
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public static function getFeeSplittingDetailByParams($params)
    {
        $orderId = array_get($params, 'orderId', '');
        $status = array_get($params, 'status', '');
        $type = array_get($params, 'type', '');
        $originId = array_get($params, 'originId', '');
        $originType = array_get($params, 'originType', '');

        $query = FeeSplittingRecord::query();
        if ($originId) {
            $query->where('origin_id', $originId);
        }
        if ($originType) {
            $query->where('origin_type', $originType);
        }
        if ($orderId) {
            $query->where('order_id', $orderId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($type) {
            $query->where('type', $type);
        }
        $feeSplittingRecord = $query->first();

        return $feeSplittingRecord;
    }

    /**
     * 通过商户id获取商户的分利比例 再获取用户自己的分润比例
     * @param $merchantId
     * @return float|int
     */
    public static function getUserFeeSplittingRatioToSelfByMerchantId($merchantId)
    {
        // 如果平台没有参与运营中心的分润, 则用户自返比例为0
        $operId = Merchant::where('id', $merchantId)->value('oper_id');
        $oper = Oper::where('id', $operId)->first();
        if($oper->pay_to_platform != Oper::PAY_TO_PLATFORM_WITH_SPLITTING){
            return 0;
        }
        $feeRatio = UserCreditSettingService::getFeeSplittingRatioToSelfSetting(); // 自反的分润比例
        $merchant = MerchantService::getById($merchantId);
        if (empty($merchant)) {
            throw new BaseResponseException('该商户不存在');
        }
        $settlementRate = $merchant->settlement_rate;
        $ratio = $feeRatio / 100 * ($settlementRate / 100 - ($settlementRate / 100 * 0.06 * 1.12 / 1.06 + $settlementRate / 100 * 0.1 * 0.25 + 0.0068));
        return $ratio;
    }

    /**
     * 获取订单纯利润 (订单毛利润-税-分润金额)
     * 分润金额 = 分给用户和商户的金额 + 分给运营中心的订单利润的50%
     * @param Order $order
     * @return float|mixed
     */
    public static function getOrderPureProfitAmountByOrder(Order $order)
    {
        $orderProfit = OrderService::getProfitAmount($order);
        $feeSplittingAmountOfUserAndMerchant = FeeSplittingRecord::where('order_id', $order->id)
            ->whereIn('origin_type', [FeeSplittingRecord::ORIGIN_TYPE_USER, FeeSplittingRecord::ORIGIN_TYPE_MERCHANT])
            ->sum('amount');
        $feeSplittingAmountOfOper = $orderProfit * 0.5;
        $orderPureProfit = $orderProfit - $feeSplittingAmountOfUserAndMerchant - $feeSplittingAmountOfOper;

        return $orderPureProfit;
    }

    /**
     * 获取分润记录列表
     * @param $params
     * @param int $pageSize
     * @param bool $withQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder
     */
    public static function getFeeSplittingRecordList($params, $pageSize = 15, $withQuery = false)
    {
        $originId = array_get($params, 'originId', '');
        $originType = array_get($params, 'originType', '');
        $orderId = array_get($params, 'orderId', '');
        $orderNo = array_get($params, 'orderNo', '');
        $type = array_get($params, 'type', '');
        $status = array_get($params, 'status', '');

        $query = FeeSplittingRecord::query();
        if ($originId) {
            $query->where('origin_id', $originId);
        }
        if ($orderId) {
            $query->where('order_id', $orderId);
        }
        if ($orderNo) {
            $query->where('order_no', $orderNo);
        }
        if ($originType) {
            $query->where('origin_type', $originType);
        }
        if (!empty($type)) {
            if (is_array($type)) {
                $query->whereIn('type', $type);
            } else {
                $query->where('type', $type);
            }
        }
        if (!empty($status)) {
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        }
        $query->orderBy('id', 'desc');

        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function ($item) {
                if ($item->origin_type == FeeSplittingRecord::ORIGIN_TYPE_USER) {
                    $user = UserService::getUserById($item->origin_id);
                    $item->name = empty($user) ? '' : ($user->name ?: $user->mobile);
                } elseif ($item->origin_type == FeeSplittingRecord::ORIGIN_TYPE_MERCHANT) {
                    $item->name = MerchantService::getNameById($item->origin_id) ?: '';
                } elseif ($item->origin_type == FeeSplittingRecord::ORIGIN_TYPE_OPER) {
                    $item->name = OperService::getNameById($item->origin_id) ?: '';
                } elseif ($item->origin_type == FeeSplittingRecord::ORIGIN_TYPE_BIZER) {
                    $bizer = BizerService::getById($item->origin_id);
                    $item->name = empty($bizer) ? '' : ($bizer->name ?: $bizer->mobile);
                } else {
                    $item->name = '';
                }
            });

            return $data;
        }
    }

    /**
     * 通过ID获取记录
     * @param $id
     * @return FeeSplittingRecord
     */
    public static function getById($id)
    {
        $feeSplittingRecord = FeeSplittingRecord::find($id);

        return $feeSplittingRecord;
    }

    /**
     * 重新分润时，获取运营中心的分润比例
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param Order $order
     * @return float|int|string
     */
    public static function getReFeeSplittingRatio(FeeSplittingRecord $feeSplittingRecord, Order $order)
    {
        $operFeeRatio = 0;  // 运营中心分润比例
        $bizerFeeRatio = 0; // 业务员分润比例

        if ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_OPER || $feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_BIZER) {
            // 通过订单中的merchant_id查找该商户的运营中心（准确点）
            $merchant = MerchantService::getById($order->merchant_id);
            $oper = OperService::getById($merchant->oper_id);
            if (empty($oper)) {
                return null;
            }
            if ($oper->pay_to_platform == Oper::PAY_TO_OPER) {
                return null;
            }

            $operFeeRatio = $operFeeRatioInit = UserCreditSettingService::getFeeSplittingRatioToOper($oper);
            $bizerFeeRatio = 0; // 业务员分润比例
            if ($operFeeRatioInit == null || $operFeeRatioInit <= 0) {
                return null;
            }
            $bizer = BizerService::getById($order->bizer_id);
            if (!empty($bizer)) {
                if (!$order->bizer_divide) {
                    $param = [
                        'operId' => $oper->id,
                        'bizerId' => $bizer->id,
                    ];
                    $operBizer = OperBizerService::getOperBizerByParam($param);
                    $order->bizer_divide = $operBizer->divide;
                    $order->save();
                }

                $bizerFeeRatio = $operFeeRatioInit * $order->bizer_divide / 100;
                $operFeeRatio = $operFeeRatioInit - $bizerFeeRatio;
                if ($operFeeRatio < 0) {
                    return null;
                }
            }
        }

        if ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_SELF) {
            // 1 自反比例
            $feeRatio = UserCreditSettingService::getFeeSplittingRatioToSelfSetting(); // 自反的分润比例
        } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_PARENT) {
            // 2 返上级比例
            if ($feeSplittingRecord->origin_type == FeeSplittingRecord::ORIGIN_TYPE_USER) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfUserSetting();
            } else if ($feeSplittingRecord->origin_type == FeeSplittingRecord::ORIGIN_TYPE_MERCHANT) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfMerchantSetting($feeSplittingRecord->merchant_level);
            } else if ($feeSplittingRecord->origin_type == FeeSplittingRecord::ORIGIN_TYPE_OPER) {
                $feeRatio = UserCreditSettingService::getFeeSplittingRatioToParentOfOperSetting();
            } else {
                throw new BaseResponseException();
            }
        } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_OPER) {
            // 3 运营中心分润比例
            $feeRatio = $operFeeRatio;
        } elseif ($feeSplittingRecord->type == FeeSplittingRecord::TYPE_TO_BIZER) {
            // 4 业务员分润比例
            $feeRatio = $bizerFeeRatio;
        }
        else {
            throw new ParamInvalidException('分润类型错误');
        }
        return $feeRatio;
    }

    /**
     * 更新分润记录
     * @param FeeSplittingRecord $feeSplittingRecord
     * @param $profitAmount
     * @param $ratio
     * @return FeeSplittingRecord
     */
    public static function updateFeeSplittingRecord(FeeSplittingRecord $feeSplittingRecord, $profitAmount, $ratio)
    {
        $newFeeSplittingRecord = self::getById($feeSplittingRecord->id);
        $newFeeSplittingRecord->order_profit_amount = $profitAmount;
        $newFeeSplittingRecord->ratio = $ratio;
        $newFeeSplittingRecord->amount = floor($profitAmount * $ratio) / 100;
        $newFeeSplittingRecord->save();

        return $newFeeSplittingRecord;
    }
}