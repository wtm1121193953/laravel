<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/27/027
 * Time: 14:33
 */
namespace App\Support\AgentPay;

use App\Exceptions\ParamInvalidException;
use App\Modules\Payment\AgentPay;

abstract class AgentPayBase
{
    protected $_configs = [];
    protected $_class_name = '';
    public function __construct()
    {
        $m = AgentPay::where('class_name',$this->_class_name)->first();

        if (empty($m)) {
            throw new ParamInvalidException('代付类名配置错误');
        }

        $this->_configs = json_decode($m->configs, true);
    }
}