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

    protected $__configs = [];

    public function __construct()
    {

    }

    abstract public function doNotify();
}