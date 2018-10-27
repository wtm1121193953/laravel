<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/27/027
 * Time: 14:32
 */
namespace App\Support\AgentPay;

class Reapal extends AgentPayBase
{
    public function __construct()
    {
        $this->__config['k1'] = 'v1';
        parent::__construct();
    }
}