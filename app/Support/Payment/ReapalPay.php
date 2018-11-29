<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 16:38
 */
namespace App\Support\Payment;

use App\Modules\Log\LogDbService;
use App\Modules\Log\LogOrderNotifyReapal;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\User\User;
use App\Support\Reapal\ReapalUtils;

class ReapalPay extends PayBase
{

    public function doNotify()
    {
        $reapal = request()->getContent();
        //$reapal = 'data=%7B%22notify_id%22%3A%223bf4cce100a94544ab65bcbd80fa5613%22%2C%22open_id%22%3A%22oA7-Z5blKW1JGt2Cf7c8LRvmpe9s%22%2C%22order_no%22%3A%22O20180830203036729649%22%2C%22order_time%22%3A%222018-08-30+20%3A30%3A37%22%2C%22sign%22%3A%22ff61f3abc45c9b3a7533a20b59292d79%22%2C%22status%22%3A%22TRADE_FINISHED%22%2C%22store_name%22%3A%22%E7%A8%8B%E7%A8%8B%E5%AE%B6%22%2C%22store_phone%22%3A%2215989438364%22%2C%22total_fee%22%3A%221%22%2C%22trade_no%22%3A%2210180830003914450%22%7D&merchant_id=100000001304038&encryptkey=';
        if (empty($reapal)) {
            return '';
        }
        LogDbService::reapalNotify(LogOrderNotifyReapal::TYPE_PAY,$reapal);

        parse_str($reapal,$url_params_arr);
        $data = json_decode($url_params_arr['data'],true);

        $reapalMap = new ReapalUtils();
        $sign = $reapalMap->createSign($data, $this->apiKey);

        if ($data['sign'] == $sign) {
            if (!empty($data['trade_no']) && $data['status'] == 'TRADE_FINISHED') {
                OrderService::paySuccess($data['order_no'], $data['trade_no'], $data['total_fee'] / 100,Order::PAY_TYPE_REAPAL);
                echo 'success';
            } else {
                echo 'fail';
            }
        } else {
            echo 'sign error';
        }
    }

    /**
     * 下单
     * @param User $user
     * @param Order $order
     * @return mixed
     */
    public function buy(User $user, Order $order)
    {
        // TODO: Implement buy() method.
    }

    /**
     * 订单退款
     * @param Order $order
     * @return mixed
     */
    public function refund(Order $order)
    {
        // TODO: Implement refund() method.
    }
}