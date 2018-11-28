<?php
/**
 * 支付方式对接的抽象类
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 16:41
 */
namespace App\Support\Payment;

use App\Modules\Order\Order;
use App\Modules\User\User;

abstract class PayBase
{

    protected $_configs = [];


    const APP_TYPE_ANDROID = 1;
    const APP_TYPE_IOS = 2;
    const APP_TYPE_MINIPROGRAM = 3;

    public function __construct()
    {

    }

    abstract public function doNotify();

    /**
     * 下单
     * @param User $user
     * @param Order $order
     * @return mixed
     */
    abstract public function buy(User $user, Order $order);

    /**
     * 订单退款
     * @param Order $order
     * @return mixed
     */
    abstract public function refund(Order $order);

}