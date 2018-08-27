<?php

namespace App\Jobs;

use App\Exceptions\BaseResponseException;
use App\Modules\Settlement\SettlementPayBatch;
use App\Modules\Settlement\SettlementPlatform;
use App\Modules\Settlement\SettlementPlatformService;
use App\Support\ReapalPay;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     */
    public function handle()
    {
        //打款批次号
        $batch_no = SettlementPlatformService::genSettlementNo();
        //批次总数量
        $batch_count = count($this->settlementIds);
        //批次总金额(商家实际收到的金额)
        $batch_amount = SettlementPlatformService::getAmountById($this->settlementIds);

        foreach ($this->settlementIds as $key => $val) {
            $settlement = SettlementPlatform::with('merchant:id,bank_card_type')->findOrFail($val);

            //更新打款状态为已打款、更新批次号
            $settlement->status = 2;
            $settlement->pay_batch_no = $batch_no;
            $settlement->save();

        }
        $res = new SettlementPayBatch();
        $res->batch_no = $batch_no;
        $res->batch_count = $batch_count;
        $res->batch_amount = $batch_amount;
        $res->save();

        //发起支付
        $contArr = [];
        foreach ($this->settlementIds as $key => $val) {
            //$data = SettlementPlatformService::getListById($val);
            $settlement = SettlementPlatform::with('merchant:id,bank_card_type')->findOrFail($val);
            if($settlement){
                $content = [
                    'cid' => $settlement->id,
                    'bank_card_no' => $settlement->bank_card_no,
                    'bank_open_name' => $settlement->bank_open_name,
                    'sub_bank_name' => $settlement->sub_bank_name,
                    'bank_open_address' => $settlement->bank_open_address,
                    'bank_open_address_branch' => '',
                    'bank_public_or_private' => $settlement->bank_card_type == 1 ? '公' : '私',
                    'real_amount' => $settlement->real_amount,
                    'currency' => '',
                    'bank_province' => '',
                    'bank_city' => '',
                    'bank_mobile' => '',
                    'ID_type' => '',
                    'ID_card_NO' => '',
                    'user_accord' => '',
                    'merchant_id' => '',
                    'remark' => '',
                    'member_id' => '',
                    'bind_bank_id' => ''
                ];
                $res = array_divide($content);
                array_push($contArr,implode(',',$res[1]));

            }
            $content = implode('|',$contArr);
        }
        $reapal =  new ReapalPay();
        $reapal->agentpay($batch_no,$batch_count,$batch_amount,$content);

    }
}
