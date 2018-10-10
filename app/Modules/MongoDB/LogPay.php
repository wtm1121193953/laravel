<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/5/005
 * Time: 14:08
 */
namespace App\Modules\MongoDB;

class LogPay extends Base
{

    protected $collection_name = 'log_pay';

    public function __construct()
    {
        parent::__construct();
    }



}