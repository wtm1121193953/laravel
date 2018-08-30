<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/24
 * Time: 20:17
 */

namespace App\Http\Controllers;

use App\Modules\Settlement\SettlementPlatformService;
use App\Support\Reapal\ReapalAgentPay;

/**
 * 代付相关控制器
 * Class AgentPayController
 * @package App\Http\Controllers
 */
class AgentPayController extends Controller
{
    /**
     * 融宝代付异步通知接口
     */
    public function notifyAgentRealpay()
    {
        $reapalAgentPay = new ReapalAgentPay();
        $resultArr = $reapalAgentPay->agentNotify();

        //"data":"交易日期，批次号,序号,银行账户,开户名,分行,支行,开户行,公/私,金额,币种,备注,商户订单号,交易反馈,失败原因"
        $arraykey = [
            'trade_date', 'batch_no', 'serial_num', 'bank_account', 'bank_name', 'bank_branch', 'bank_sub_branch', 'opening_bank', 'bank_public_or_private', 'amount', 'currency', 'remark', 'merchant_num', 'return_msg', 'error_message'
        ];
        $res = array_combine($arraykey, $resultArr);

        $settlement = SettlementPlatformService::getAmountByPayBatchNo($res['serial_num'],$res['batch_no']);
        if($settlement){
            if($res['return_msg'] == '成功'){
                $settlement->status = 3;
            }elseif ($res['return_msg'] == '失败'){
                $settlement->status = 5;
                $settlement->reason = $res['return_msg'];
            }
            $settlement->save();
        }
        return 'success';
    }
}