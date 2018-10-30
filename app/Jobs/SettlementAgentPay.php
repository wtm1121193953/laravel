<?php

namespace App\Jobs;

use App\Modules\Settlement\SettlementPayBatch;
use App\Modules\Settlement\SettlementPlatform;
use App\Modules\Settlement\SettlementPlatformService;
use App\Support\AgentPay\Reapal;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementAgentPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $settlementIds;
    /**
     * Create a new job instance.
     *
     * @param $settlementIds
     */
    public function __construct($settlementIds)
    {
        $this->settlementIds = $settlementIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //打款批次号
        Log::info('开始执行自动打款任务， 执行结算单ID：', ['settlementIds' => $this->settlementIds]);
        $batch_no = SettlementPlatformService::genSettlementNo();

        $list = SettlementPlatform::whereIn('id', $this->settlementIds)->get();
        $batch_count = $list->count();
        $batch_amount = $list->pluck('real_amount')->sum();

        DB::beginTransaction();
        try {

            $payBatch = new SettlementPayBatch();
            $payBatch->batch_no = $batch_no;
            $payBatch->batch_count = $batch_count;
            $payBatch->batch_amount = $batch_amount;
            $payBatch->save();

            foreach ($list as $settlement){

                //更新打款状态为已打款、更新批次号
                $settlement->status = SettlementPlatform::STATUS_PAYING;
                $settlement->pay_batch_no = $batch_no;
                $settlement->settlement_pay_batch_id = $payBatch->id;
                $settlement->save();
            }

            $contArr = [];
            foreach ($list as $settlement) {
                $item = [
                    '序号' => $settlement->id,
                    '银行账户' => $settlement->bank_card_no,
                    '开户名' => $settlement->bank_open_name,
                    '开户行' => implode('|',$settlement->sub_bank_name)[0],
                    '分行' => implode('|',$settlement->bank_open_address)[1],
                    '支行' => implode('|',$settlement->sub_bank_name)[1],
                    '公/私' => $settlement->bank_card_type == 1 ? '公' : '私',
                    '金额' => $settlement->real_amount,
                    '币种' => '',
                    '省' => '',
                    '市' => '',
                    '手机号' => '',
                    '证件类型' => '',
                    '证件号' => '',
                    '用户协议号' => '',
                    '商户订单号' => $settlement->settlement_no,
                    '备注' => '',
                    '会员号' => '',
                    '绑卡Id' => ''
                ];
                $contArr[] = implode(',', array_values($item));
            }
            $content = implode('|',$contArr);
            $reapal =  new Reapal();
            $result = $reapal->agentpay($batch_no,$batch_count,$batch_amount,$content);

            $batch = SettlementPayBatch::where('id', $payBatch->id)->first();
            if ($result['result_code'] == '0000') {
                $batch->status = SettlementPayBatch::STATUS_IS_SUBMIT;
            } else {
                $batch->error_code = $result['result_code'] ?? '';
                $batch->error_msg = $result['result_msg'] ?? '';
            }
            $batch->save();

            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }
}
