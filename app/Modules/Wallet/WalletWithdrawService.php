<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Bizer\Bizer;
use App\Modules\Bizer\BizerService;
use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserCreditSettingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 提现相关Service
 * Class WalletWithdrawService
 * @package App\Modules\Wallet
 */
class WalletWithdrawService extends BaseService
{

    /**
     * 获取可提现的日期数组, 返回每月的哪些天
     * @return array
     */
    public static function getWithdrawableDays()
    {
        return [10, 20, 30];
    }

    /**
     * 根据id获取提现记录
     * @param $id
     * @param array $fields
     * @return WalletWithdraw
     */
    public static function getWalletWithdrawById($id, $fields = ['*'])
    {
        $walletWithdraw = WalletWithdraw::find($id, $fields);

        return $walletWithdraw;
    }

    /**
     * 生成 钱包提现流水单号
     * @return string
     */
    public static function createWalletWithdrawNo()
    {
        $billNo = 'W'. date('Ymd') .substr(time(), -6, 6). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return $billNo;
    }

    /**
     * 校验提现密码
     * @param $withdrawPassword
     * @param $originId
     * @param $originType
     * @return bool
     */
    public static function checkWithdrawPasswordByOriginInfo($withdrawPassword, $originId, $originType)
    {
        $wallet = WalletService::getWalletInfoByOriginInfo($originId, $originType);
        if (!$wallet->withdraw_password) {
            throw new BaseResponseException('请设置提现密码');
        }
        $checkPass = Wallet::genPassword($withdrawPassword, $wallet->salt);
        if ($wallet->withdraw_password == $checkPass) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 创建 提现记录 并更新钱包可提现余额
     * @param Wallet $wallet
     * @param Merchant|Oper|User|Bizer|CsMerchant $obj
     * @param $amount
     * @param $param
     * @return WalletWithdraw
     * @throws \Exception
     */
    public static function createWalletWithdrawAndUpdateWallet(Wallet $wallet, $obj, $amount, $param = [])
    {
        $invoiceExpressCompany = array_get($param, 'invoiceExpressCompany', '');
        $invoiceExpressNo = array_get($param, 'invoiceExpressNo', '');

        if ($wallet->status == Wallet::STATUS_OFF) {
            throw new BaseResponseException('钱包已冻结，暂不支持提现');
        }
        if ($wallet->balance - $amount < 0) {
            throw new BaseResponseException('钱包余额不足');
        }

        if ($obj instanceof User) {
            $ratio = UserCreditSettingService::getUserWithdrawChargeRatio();
        } elseif ($obj instanceof Merchant) {
            $ratio = UserCreditSettingService::getMerchantWithdrawChargeRatioByBankCardType($obj->bank_card_type);
        } elseif ($obj instanceof Oper) {
            $ratio = UserCreditSettingService::getOperWithdrawChargeRatio();
            $obj->bank_card_type = 1;
        } elseif ($obj instanceof Bizer) {
            $ratio = UserCreditSettingService::getBizerWithdrawChargeRatio();
            $bizerBankCard = BankCardService::getBankCardByOriginInfo($obj->id, BankCard::ORIGIN_TYPE_BIZER);
            $obj->bank_card_type = $bizerBankCard->bank_card_type;
            $obj->bank_open_name = $bizerBankCard->bank_card_open_name;
            $obj->bank_card_no = $bizerBankCard->bank_card_no;
            $obj->sub_bank_name = $bizerBankCard->bank_name;
        } elseif ($obj instanceof CsMerchant) {
            $ratio = UserCreditSettingService::getMerchantWithdrawChargeRatioByBankCardType($obj->bank_card_type);
        } else {
            throw new BaseResponseException('用户类型错误');
        }

        DB::beginTransaction();
        try{
            // 1.创建提现记录
            $withdraw = new WalletWithdraw();
            $withdraw->wallet_id = $wallet->id;
            $withdraw->origin_id = $wallet->origin_id;
            $withdraw->origin_type = $wallet->origin_type;
            $withdraw->withdraw_no = self::createWalletWithdrawNo();
            $withdraw->amount = $amount;
            $withdraw->charge_amount = number_format($amount * $ratio / 100, 2, '.', '');
            $withdraw->remit_amount = $amount - $withdraw->charge_amount;
            $withdraw->status = WalletWithdraw::STATUS_AUDITING;
            $withdraw->invoice_express_company = $invoiceExpressCompany;
            $withdraw->invoice_express_no = $invoiceExpressNo;
            $withdraw->bank_card_type = $obj->bank_card_type;
            $withdraw->bank_card_open_name = $obj->bank_open_name;
            $withdraw->bank_card_no = $obj->bank_card_no;
            $withdraw->bank_name = $obj->sub_bank_name;
            $withdraw->save();

            // 2.更新钱包余额
            $wallet->balance = $wallet->balance - $amount;
            $wallet->save();

            // 3.创建钱包流水记录
            $walletBill = new WalletBill();
            $walletBill->wallet_id = $wallet->id;
            $walletBill->origin_id = $wallet->origin_id;
            $walletBill->origin_type = $wallet->origin_type;
            $walletBill->bill_no = WalletService::createWalletBillNo();
            $walletBill->type = WalletBill::TYPE_WITHDRAW;
            $walletBill->obj_id = $withdraw->id;
            $walletBill->inout_type = WalletBill::OUT_TYPE;
            $walletBill->amount = $amount;
            $walletBill->amount_type = WalletBill::AMOUNT_TYPE_UNFREEZE;
            $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
            $walletBill->after_balance = $wallet->balance;
            $walletBill->save();

            DB::commit();

            return $withdraw;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('提现失败', [
                'message' => $e->getMessage(),
                'data' => $e
            ]);
            throw $e;
        }
    }

    /**
     * admin 获取提现记录
     * @param $param
     * @param int $pageSize
     * @param bool $withQuery
     * @return WalletWithdraw|\Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder
     */
    public static function getWithdrawRecords($param, $pageSize = 15, $withQuery = false)
    {
        $query = self::parseWithdrawQuery($param)->orderBy('created_at', 'desc');
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function($item) {
                if ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_USER) {
                    $user = UserService::getUserById($item->origin_id);
                    $item->user_mobile = $user->mobile;
                } elseif ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
                    $merchant = MerchantService::getById($item->origin_id);
                    $item->merchant_name = $merchant->name;
                    $item->oper_name = OperService::getNameById($merchant->oper_id);
                } elseif ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_OPER) {
                    $item->oper_name = OperService::getNameById($item->origin_id);
                } elseif ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_BIZER) {
                    $bizer = BizerService::getById($item->origin_id);
                    $item->bizer_name = !empty($bizer) ? $bizer->name : '';
                    $item->bizer_mobile = !empty($bizer) ? $bizer->mobile : '';
                }
            });
            return $data;
        }
    }

    /**
     * 获取提现数据汇总
     * @param array $params
     * @return array
     */
    public static function getWithdrawTotalAmountAndCount($params = [])
    {
        $query = self::parseWithdrawQuery($params);
        $count = $query->count();
        $amount = $query->sum('amount');

        return [
            'count' => $count,
            'amount' => $amount,
        ];
    }

    /**
     * 根据参数组装 query
     * @param $params
     * @return Builder
     */
    private static function parseWithdrawQuery($params)
    {
        $start = array_get($params, 'start');
        $end = array_get($params, 'end');
        $originType = array_get($params, 'originType');
        $originId = array_get($params, 'originId');
        $status = array_get($params, 'status');
        $userMobile = array_get($params, 'userMobile');
        $merchantName = array_get($params, 'merchantName');
        $operName = array_get($params, 'operName');
        $bizerMobile = array_get($params, 'bizerMobile');
        $withdrawNo = array_get($params, 'withdrawNo');
        $bankCardType = array_get($params, 'bankCardType');
        $batchId = array_get($params, 'batchId');

        $query = WalletWithdraw::query();

        if($withdrawNo){
            $query->where('withdraw_no', 'like', "%$withdrawNo%");
        }

        if($originType){
            $query->where('origin_type', $originType);
        }
        if($originId){
            $query->where('origin_id', $originId);
        }
        if($bankCardType){
            $query->where('bank_card_type', $bankCardType);
        }
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }
        if($originType == WalletWithdraw::ORIGIN_TYPE_USER && $userMobile){
            $originIds = UserService::getUserColumnArrayByMobile($userMobile, 'id');
        }
        if($originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT && $merchantName){
            $originIds = MerchantService::getMerchantColumnArrayByParams(compact('merchantName'), 'id');
        }
        if ($originType == WalletWithdraw::ORIGIN_TYPE_OPER && $operName) {
            $originIds = OperService::getOperColumnArrayByOperName($operName, 'id');
        }
        if ($originType == WalletWithdraw::ORIGIN_TYPE_BIZER && $bizerMobile) {
            $originIds = BizerService::getBizerColumnArrayByParams(['bizerMobile' => $bizerMobile], 'id');
        }
        if ($originType == WalletWithdraw::ORIGIN_TYPE_CS && $merchantName && $operName) {
            $originIds1 = CsMerchantService::getMerchantColumnArrayByParams(compact('merchantName'), 'id')->toArray();
            $operIds = OperService::getOperColumnArrayByOperName($operName, 'id');
            $originIds2 = CsMerchantService::getMerchantColumnArrayByParams(compact('operIds'), 'id')->toArray();
            $originIds = array_intersect($originIds1, $originIds2);
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_CS && $merchantName) {
            $originIds = CsMerchantService::getMerchantColumnArrayByParams(compact('merchantName'), 'id');
        } elseif ($originType == WalletWithdraw::ORIGIN_TYPE_CS && $operName) {
            $operIds = OperService::getOperColumnArrayByOperName($operName, 'id');
            $originIds = CsMerchantService::getMerchantColumnArrayByParams(compact('operIds'), 'id');
        }
        if(isset($originIds)){
            $query->whereIn('origin_id', $originIds);
        }
        if($status){
            if(is_array($status) || $status instanceof Collection){
                $query->whereIn('status', $status);
            }else {
                $query->whereIn('status', explode(',',$status));
            }
        }
        if($start && $start instanceof Carbon){
            $start = $start->format('Y-m-d H:i:s');
        }
        if($end && $end instanceof Carbon){
            $end = $end->format('Y-m-d H:i:s');
        }
        if($start && $end){
            $query->whereBetween('created_at', [$start, $end]);
        }else if($start){
            $query->where('created_at', '>', $start);
        }else if($end){
            $query->where('created_at', '<', $end);
        }
        return $query;
    }

    /**
     * 通过id 获取提现记录详情
     * @param $id
     * @return WalletWithdraw
     */
    public static function getWalletWithdrawDetailById($id)
    {
        $withdraw = WalletWithdraw::find($id);
        if (empty($withdraw)) {
            throw new BaseResponseException('该提现记录不存在');
        }

        if ($withdraw->origin_type == WalletWithdraw::ORIGIN_TYPE_USER) {
            $withdraw->user_mobile = UserService::getUserById($withdraw->origin_id)->mobile;
        } elseif ($withdraw->origin_type == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
            $merchant = MerchantService::getById($withdraw->origin_id);
            $withdraw->merchant_name = $merchant->name;
            $withdraw->oper_id = $merchant->oper_id;
            $withdraw->oper_name = OperService::getNameById($merchant->oper_id);
        } elseif ($withdraw->origin_type == WalletWithdraw::ORIGIN_TYPE_OPER) {
            $withdraw->oper_name = OperService::getNameById($withdraw->origin_id);
        } elseif ($withdraw->origin_type == WalletWithdraw::ORIGIN_TYPE_BIZER) {
            $bizer = BizerService::getById($withdraw->origin_id);
            $withdraw->bizer_name = $bizer->name;
            $withdraw->bizer_mobile = $bizer->mobile;
        } else {
            throw new BaseResponseException('该提现记录用户类型不存在');
        }

        if (in_array($withdraw->status, [WalletWithdraw::STATUS_AUDITING, WalletWithdraw::STATUS_AUDIT, WalletWithdraw::STATUS_WITHDRAW])) {
            $walletBill = WalletBill::where('type', WalletBill::TYPE_WITHDRAW)
                ->where('obj_id', $withdraw->id)
                ->first();
        } elseif (in_array($withdraw->status, [WalletWithdraw::STATUS_WITHDRAW_FAILED, WalletWithdraw::STATUS_AUDIT_FAILED])) {
            $walletBill = WalletBill::where('type', WalletBill::TYPE_WITHDRAW_FAILED)
                ->where('obj_id', $withdraw->id)
                ->first();
        } else {
            throw new BaseResponseException('该提现状态不存在');
        }
        $withdraw->after_amount = isset($walletBill->after_amount) ? $walletBill->after_amount : '未知';
        $withdraw->after_balance = isset($walletBill->after_balance) ? $walletBill->after_balance : '未知';

        return $withdraw;
    }

    /**
     * 提现审核成功操作
     * @param WalletWithdraw $walletWithdraw
     * @param $batchId
     * @param string $remark
     * @return WalletWithdraw
     * @throws \Exception
     */
    public static function auditSuccess(WalletWithdraw $walletWithdraw, $batchId, $remark = '')
    {
        DB::beginTransaction();
        try{
            // 1.更新提现批次表的总金额和总笔数
            $walletBatch = WalletBatchService::getById($batchId);
            if (empty($walletBatch)) throw new \Exception('该提现批次不存在');

            $walletBatch->amount += $walletWithdraw->amount;
            $walletBatch->total += 1;
            $walletBatch->save();
            // 2.更新提现记录表状态 和 批次id、编号
            $walletWithdraw->status = WalletWithdraw::STATUS_AUDIT;
            $walletWithdraw->batch_id = $batchId;
            $walletWithdraw->batch_no = $walletBatch->batch_no;
            $walletWithdraw->remark = $remark;
            $walletWithdraw->save();
            DB::commit();
            return $walletWithdraw;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('提现审核成功操作失败', [
                'message' => $e->getMessage(),
                'data' => $e,
            ]);
            throw new BaseResponseException('审核失败');
        }
    }

    /**
     * 提现审核不通过操作
     * @param WalletWithdraw $walletWithdraw
     * @param string $remark
     * @return WalletWithdraw
     * @throws \Exception
     */
    public static function auditFailed(WalletWithdraw $walletWithdraw, $remark = '')
    {
        DB::beginTransaction();
        try{
            self::withdrawFail($walletWithdraw, WalletWithdraw::STATUS_AUDIT_FAILED, $remark);

            DB::commit();
            return $walletWithdraw;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('审核不通过操作失败', [
                'message' => $e->getMessage(),
                'data' => $e,
            ]);
            throw new BaseResponseException('审核操作失败');
        }
    }

    /**
     * 审核不通过 和 打款失败 公用操作
     * @param WalletWithdraw $walletWithdraw
     * @param $status
     * @param string $remark
     * @throws \Exception
     */
    private static function withdrawFail(WalletWithdraw $walletWithdraw, $status, $remark = '') {
        // 1. 提现记录表 审核状态修改为 审核不通过
        $walletWithdraw->status = $status;
        $walletWithdraw->remark = $remark ?: '';
        $walletWithdraw->save();
        // 2. 更新钱包表
        $wallet = WalletService::getWalletById($walletWithdraw->wallet_id);
        if (empty($wallet)) throw new \Exception('该钱包不存在');
        $wallet->balance += $walletWithdraw->amount;
        $wallet->save();
        // 3. 添加钱包流水表 提现失败记录
        $walletBill = new WalletBill();
        $walletBill->wallet_id = $wallet->id;
        $walletBill->origin_id = $wallet->origin_id;
        $walletBill->origin_type = $wallet->origin_type;
        $walletBill->bill_no = WalletService::createWalletBillNo();
        $walletBill->type = WalletBill::TYPE_WITHDRAW_FAILED;
        $walletBill->obj_id = $walletWithdraw->id;
        $walletBill->inout_type = WalletBill::IN_TYPE;
        $walletBill->amount = $walletWithdraw->amount;
        $walletBill->amount_type = WalletBill::AMOUNT_TYPE_UNFREEZE;
        $walletBill->after_amount = $wallet->balance + $wallet->freeze_balance;
        $walletBill->after_balance = $wallet->balance;
        $walletBill->save();
    }

    /**
     * admin 打款成功操作 单独或者批量
     * @param $ids
     * @throws \Exception
     */
    public static function paySuccess($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $amount = 0;
        $total = 0;
        $batchId = 0;
        DB::beginTransaction();
        try {

            foreach ($ids as $id) {
                $walletWithdraw = self::getWalletWithdrawById($id);
                if ($walletWithdraw->status == WalletWithdraw::STATUS_AUDIT) {
                    $walletWithdraw->status = WalletWithdraw::STATUS_WITHDRAW;
                    $walletWithdraw->save();
                    $amount += $walletWithdraw->amount;
                    $total += 1;
                    $batchId = $walletWithdraw->batch_id;
                }
            }
            // 因为是同一批次或者是单个的，所以批次表就更新一次
            $walletBatch = WalletBatchService::getById($batchId);
            $walletBatch->success_amount += $amount;
            $walletBatch->success_total += $total;
            $walletBatch->save();
            // 更新批次表后，判断该批次的提现订单是否都已经打款 且 该批次状态为准备打款，如果是则 修改状态为 打款完成
            if (WalletBatchService::checkBatchPayOrNot($batchId) && $walletBatch->status == WalletBatch::STATUS_PREPARE_WITHDRAW) {
                WalletBatchService::changeStatus($walletBatch, WalletBatch::STATUS_WITHDRAW_SUCCESS);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('打款成功操作失败', [
                'message' => $e->getMessage(),
                'data' => $e,
            ]);
            throw new BaseResponseException('打款成功操作失败');
        }
    }

    /**
     * 打款失败的操作 单个或者批量
     * @param $ids
     * @param string $remark
     * @throws \Exception
     */
    public static function payFail($ids, $remark = '')
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $amount = 0;
        $total = 0;
        $batchId = 0;
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $walletWithdraw = self::getWalletWithdrawById($id);
                if ($walletWithdraw->status == WalletWithdraw::STATUS_AUDIT) {
                    self::withdrawFail($walletWithdraw, WalletWithdraw::STATUS_WITHDRAW_FAILED, $remark);

                    $amount += $walletWithdraw->amount;
                    $total += 1;
                    $batchId = $walletWithdraw->batch_id;
                }
            }
            // 因为是同一批次或者是单个的，所以批次表就更新一次
            $walletBatch = WalletBatchService::getById($batchId);
            $walletBatch->failed_amount += $amount;
            $walletBatch->failed_total += $total;
            $walletBatch->save();
            // 更新批次表后，判断该批次的提现订单是否都已经打款 且 该批次状态为准备打款，如果是则 修改状态为 打款完成
            if (WalletBatchService::checkBatchPayOrNot($batchId) && $walletBatch->status == WalletBatch::STATUS_PREPARE_WITHDRAW) {
                WalletBatchService::changeStatus($walletBatch, WalletBatch::STATUS_WITHDRAW_SUCCESS);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('打款失败的操作失败', [
                'message' => $e->getMessage(),
                'data' => $e,
            ]);
            throw new BaseResponseException('打款失败的操作失败');
        }
    }
}