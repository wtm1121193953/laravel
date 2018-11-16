<?php

namespace App\Modules\Merchant;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Oper\OperService;
use Carbon\Carbon;

class MerchantElectronicContractService extends BaseService
{

    /**
     * 通过商户ID获取商户电子合同
     * @param $merchantId
     * @return MerchantElectronicContract
     */
    public static function getContractByMerchantId($merchantId)
    {
        $contract = MerchantElectronicContract::where('merchant_id', $merchantId)
            ->first();

        return $contract;
    }

    /**
     * 创建商户电子合同
     * @param $merchantId
     * @return MerchantElectronicContract
     */
    public static function createdElectronicContract($merchantId)
    {
        $merchant = MerchantService::getById($merchantId);
        if (empty($merchant)) {
            throw new BaseResponseException('该商户不存在');
        }
        $oper = OperService::getById($merchant->oper_id);
        if (empty($oper)) {
            throw new BaseResponseException('该商户的运营中心不存在');
        }
        $contract = new MerchantElectronicContract();
        $contract->merchant_id = $merchantId;
        $contract->save();
        $contract->el_contract_no = $oper->number . date('Ymd', time()) . $contract->id;
        $contract->save();

        return $contract;
    }

    /**
     * 更新电子合同
     * @param $id
     * @return MerchantElectronicContract
     */
    public static function updateElectronicContract($id)
    {
        $contract = self::getById($id);
        $contract->sign_time = Carbon::now();
        $contract->expiry_time = $contract->sign_time->copy()->addYear(1)->subDay();
        $contract->save();

        return $contract;
    }

    /**
     * 通过id获取合同
     * @param $id
     * @param bool $withMerchant
     * @return MerchantElectronicContract
     */
    public static function getById($id, $withMerchant = false)
    {
        if ($withMerchant) {
            $contract = MerchantElectronicContract::with('merchant')->find($id);
        } else {
            $contract = MerchantElectronicContract::find($id);
        }
        return $contract;
    }
}