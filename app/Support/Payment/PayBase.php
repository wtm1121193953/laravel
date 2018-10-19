<?php
/**
 * 支付方式对接的抽象类
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 16:41
 */
namespace App\Support\Payment;

abstract class PayBase
{

    protected $_configs = [];

    public $_app_type = 0;

    const APP_TYPE_ANDROID = 1;
    const APP_TYPE_IOS = 2;
    const APP_TYPE_MINIPROGRAM = 3;

    public function __construct($app_type)
    {

        $this->_app_type = $app_type;
    }

    abstract public function doNotify();
}