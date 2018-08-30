<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/24
 * Time: 20:17
 */

namespace App\Http\Controllers;

use App\Modules\Settlement\SettlementPlatform;
use App\Modules\Settlement\SettlementPlatformService;
use App\Result;
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
        $res = $reapalAgentPay->agentNotify();

        $settlement = SettlementPlatformService::getListByMerchantNo($res['merchant_num'],$res['batch_no']);
        if($settlement){
            if($res['return_msg'] == '成功'){
                $settlement->status = SettlementPlatform::STATUS_PAID;
            }elseif ($res['return_msg'] == '失败'){
                $settlement->status = SettlementPlatform::STATUS_FAIL;
                $settlement->reason = $res['return_msg'];
            }
            $settlement->save();
        }
        return 'success';
    }

    /**
     * 代付批次查询
     */
    /*public function agentpayQueryBatch()
    {
        $params = [
            'trans_time' => request('trans_time'),
            'batch_no' => request('batch_no'),
            'next_tag' => request('next_tag')
        ];

        $reapalAgentPay = new ReapalAgentPay();
        $resultArr = $reapalAgentPay->agentpayQueryBatch($params);

        if($resultArr['result_code'] == '0001'){
            return Result::success($resultArr);
        }

    }*/
}