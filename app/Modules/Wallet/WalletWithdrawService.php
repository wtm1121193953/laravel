<?php

namespace App\Modules\Wallet;


use App\BaseService;

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
}