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
    protected $_configs = [];//配置信息
    protected $_class_name = '';//当前代付的类名
    protected $_angetpay_info = '';//代付信息
    public function __construct()
    {
        $this->_angetpay_info = AgentPay::where('class_name',$this->_class_name)->first();

        if (empty($this->_angetpay_info)) {
            throw new ParamInvalidException('代付类名配置错误');
        }

        if (!empty($this->_angetpay_info->configs)) {
            $this->_configs = json_decode($this->_angetpay_info->configs, true);
        }

    }
}