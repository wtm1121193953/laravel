<?php

namespace App\Modules\Wallet;


use App\BaseService;
use App\Exceptions\BaseResponseException;
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

/**
 * 提现相关Service
 * Class WalletWithdrawService
 * @package App\Modules\Wallet
 */
class WalletWithdrawService extends BaseService
{

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
        $billNo = date('Ymd') .substr(time(), -7, 7). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
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
     * 创建提现记录 并更新钱包可提现余额
     * @param Wallet $wallet
     * @param Merchant|Oper|User $obj
     * @param $amount
     * @param $param
     * @return WalletWithdraw
     */
    public static function createWalletWithdrawAndUpdateWallet(Wallet $wallet, $obj, $amount, $param)
    {
        $invoiceExpressCompany = array_get($param, 'invoiceExpressCompany', '');
        $invoiceExpressNo = array_get($param, 'invoiceExpressNo', '');

        if ($obj instanceof User) {
            $originType = WalletWithdraw::ORIGIN_TYPE_USER;
            throw new BaseResponseException('暂不支持提现');
        } elseif ($obj instanceof Merchant) {
            $originType = WalletWithdraw::ORIGIN_TYPE_MERCHANT;
            $ratio = UserCreditSettingService::getMerchantWithdrawChargeRatioByBankCardType($obj->bank_card_type);
        } elseif ($obj instanceof Oper) {
            $originType = WalletWithdraw::ORIGIN_TYPE_OPER;
            $ratio = UserCreditSettingService::getOperWithdrawChargeRatio();
            $obj->bank_card_type = 1;
        } else {
            throw new BaseResponseException('用户类型错误');
        }

        // 1.创建提现记录
        $withdraw = new WalletWithdraw();
        $withdraw->wallet_id = $wallet->id;
        $withdraw->origin_id = $obj->id;
        $withdraw->origin_type = $originType;
        $withdraw->withdraw_no = self::createWalletWithdrawNo();
        $withdraw->amount = $amount;
        $withdraw->charge_amount = number_format($amount * $ratio / 100, 2);
        $withdraw->remit_amount = number_format($amount - number_format($amount * $ratio / 100, 2), 2);
        $withdraw->status = WalletWithdraw::STATUS_AUDITING;
        $withdraw->invoice_express_company = $invoiceExpressCompany;
        $withdraw->invoice_express_no = $invoiceExpressNo;
        $withdraw->bank_card_type = $obj->bank_card_type;
        $withdraw->bank_card_open_name = $obj->bank_open_name;
        $withdraw->bank_card_no = $obj->bank_card_no;
        $withdraw->bank_name = $obj->sub_bank_name;
        $withdraw->save();

        // 2.更新钱包余额
        $wallet->balance = number_format($wallet->balance - $amount, 2);
        $wallet->save();

        return $withdraw;
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
        $query = self::parseWithdrawQuery($param);
        if ($withQuery) {
            return $query;
        } else {
            $data = $query->paginate($pageSize);
            $data->each(function($item) {
                if ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_USER) {
                    $user = UserService::getUserById($item->origin_id);
                    $item->mobile = $user->mobile;
                } elseif ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_MERCHANT) {
                    $merchant = MerchantService::getById($item->origin_id);
                    $item->merchant_name = $merchant->name;
                    $item->oper_name = OperService::getNameById($merchant->oper_id);
                } elseif ($item->origin_type == WalletWithdraw::ORIGIN_TYPE_OPER) {
                    $item->oper_name = OperService::getNameById($item->origin_id);
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
        $withdrawNo = array_get($params, 'withdrawNo');
        $bankCardType = array_get($params, 'bankCardType');

        $query = WalletWithdraw::query();

        if($withdrawNo){
            $query->where('withdraw_no', $withdrawNo);
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
        if($originType == WalletWithdraw::ORIGIN_TYPE_USER && $userMobile){
            $originIds = UserService::getUserColumnArrayByMobile($userMobile, 'id');
        }
        if($originType == WalletWithdraw::ORIGIN_TYPE_MERCHANT && $merchantName){
            $originIds = MerchantService::getMerchantColumnArrayByMerchantName($merchantName, 'id');
        }
        if ($originType == WalletWithdraw::ORIGIN_TYPE_OPER && $operName) {
            $originIds = OperService::getOperColumnArrayByOperName($operName, 'id');
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
}