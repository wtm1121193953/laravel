<?php

namespace App\Jobs;

use App\Modules\Merchant\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SettlementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $settlementCycleType;

    /**
     * Create a new job instance.
     *
     * @param $settlementCycleType
     */
    public function __construct($settlementCycleType)
    {
        //
        $this->settlementCycleType = $settlementCycleType;
    }

    /**
     * Execute the job.
     * 每周结算
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        // 计算要结算的开始日期与结束日期
        Log::info('开始执行结算任务');
        switch ($this->settlementCycleType){
            case Merchant::SETTLE_WEEKLY:
                $end = Carbon::now()->subWeek()->endOfWeek();
                $start = Carbon::now()->subWeek()->startOfWeek();
                break;
            case Merchant::SETTLE_HALF_MONTHLY:
                if(Carbon::now()->day > 15){
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->startOfMonth()->addDays(14)->endOfDay();
                } else {
                    $start = Carbon::now()->subMonth()->startOfMonth()->addDays(15);
                    $end = Carbon::now()->subMonth()->endOfMonth();
                }
                break;
            case Merchant::SETTLE_MONTHLY:
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
            case Merchant::SETTLE_HALF_YEARLY:
                if(Carbon::now()->month > 6){
                    $start = Carbon::now()->startOfYear();
                    $end = Carbon::now()->startOfYear()->addMonths(5)->endOfMonth();
                }else {
                    $start = Carbon::now()->subYear()->startOfYear()->addMonths(6)->startOfMonth();
                    $end = Carbon::now()->subYear()->endOfYear();
                }
                break;
            case Merchant::SETTLE_YEARLY:
                $start = Carbon::now()->subYear()->startOfYear();
                $end = Carbon::now()->subYear()->endOfYear();
                break;
            default :
                throw new \Exception('错误的结算方式:' . $this->settlementCycleType);
        }
        // 查询周结的商家列表
        Merchant::where('settlement_cycle_type', $this->settlementCycleType)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($start, $end){
                $merchants->each(function ($item) use ($start, $end){
                    SettlementForMerchant::dispatch($item->id, $start, $end);
                });
            });

        Log::info('结算任务执行完成');
        //
    }
}
