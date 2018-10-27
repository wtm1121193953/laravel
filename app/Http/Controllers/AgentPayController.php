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
use App\Support\AgentPay\Reapal;
use Illuminate\Support\Facades\App;

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
    public function reapalNotify()
    {
        $reapalAgentPay = new Reapal();
        $res = $reapalAgentPay->agentNotify();

        $settlement = SettlementPlatformService::getBySettlementNo($res['settlement_no']);
        if($settlement){
            if($res['return_msg'] == '成功'){
                $settlement->status = SettlementPlatform::STATUS_PAID;
                $settlement->reason = $res['return_msg'];
            }elseif ($res['return_msg'] == '失败'){
                $settlement->status = SettlementPlatform::STATUS_FAIL;
                $settlement->reason = $res['error_message'];
            }
            $settlement->save();
        }
        return 'success';
    }

    /**
     * 模拟代付通知
     */
    public function mockAgentPayNotify()
    {
        if (App::environment('production')){
            abort(404);
            return;
        }
        $settlement_no = request('settlement_no');
        $type = request('type'); // 1-成功  2-失败
        $settlement = SettlementPlatformService::getBySettlementNo($settlement_no);
        if($settlement){
            if($type == 1){
                $settlement->status = SettlementPlatform::STATUS_PAID;
                $settlement->reason = '模拟代付成功';
            }elseif ($type == 2){
                $settlement->status = SettlementPlatform::STATUS_FAIL;
                $settlement->reason = '模拟代付失败';
            }
            $settlement->save();
        }
        dd('模拟代付通知完成', $settlement);
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