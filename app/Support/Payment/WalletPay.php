<?php
/**
 * 钱包支付
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/29/029
 * Time: 14:14
 */
namespace App\Support\Payment;

use App\Modules\Order\Order;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletService;

class WalletPay
{

    /**
     * 平台购物买单
     * @param $orderInfo
     * @param $withdrawPassword
     */
    public function platformShopping($orderInfo, $withdrawPassword)
    {
        $orderNo = 'O20180726111654238936';
        $orderInfo = Order::where('order_no', $orderNo)->firstOrFail();

        $wallet = WalletService::checkPayPassword($withdrawPassword, $orderInfo->user_id);

        $rs = WalletService::minusBalance($wallet, $orderInfo->pay_price, WalletBill::TYPE_PLATFORM_SHOPPING, $orderInfo->id);


        dd($rs);
    }
}