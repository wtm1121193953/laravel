<?php

namespace App\Jobs\Schedule;

use App\Jobs\SettlementAgentPay;
use App\Modules\Settlement\Settlement;
use App\Modules\Settlement\SettlementPlatform;
use App\Modules\Settlement\SettlementPlatformService;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SettlementAgentPayDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 提取每天需要打款的结算单
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //计算昨天要结算的任务
        Log::info('开始执行每天需要打款的结算单');

        SettlementPlatform::select('id')
            ->where('status','=', SettlementPlatform::STATUS_UN_PAY)
            ->where('type','=',SettlementPlatform::TYPE_AGENT)
            ->where('settlement_cycle_type','=',SettlementPlatform::SETTLE_DAY_ADD_ONE)
            ->chunk(1000, function(Collection $list){
                SettlementAgentPay::dispatch($list->pluck('id'));
            });

        Log::info('每日打款结算单任务完成');
    }
}
