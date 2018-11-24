<?php

namespace App\Jobs\Schedule;

use App\Jobs\SettlementForMerchant;
use App\Modules\Merchant\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 每周订单结算, 旧结算逻辑, 支付到运营中心时使用此结算逻辑
 * Class SettlementWeekly
 * @package App\Jobs\Schedule
 */
class SettlementWeekly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $settlementCycleType;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @param $settlementCycleType
     * @param Carbon $date
     */
    public function __construct($settlementCycleType, $date = null)
    {
        //
        $this->settlementCycleType = $settlementCycleType;
        if(is_null($date)){
            $this->date = Carbon::now();
        }else {
            $this->date = $date;
        }
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
        $date = $this->date;
        switch ($this->settlementCycleType){
            case Merchant::SETTLE_WEEKLY:
                $end = $date->copy()->subWeek()->endOfWeek();
                $start = $date->copy()->subWeek()->startOfWeek();
                break;
            case Merchant::SETTLE_HALF_MONTHLY:
                if($date->day > 15){
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->startOfMonth()->addDays(14)->endOfDay();
                } else {
                    $start = $date->copy()->subMonth()->startOfMonth()->addDays(15);
                    $end = $date->copy()->subMonth()->endOfMonth();
                }
                break;
            case Merchant::SETTLE_DAILY_AUTO:
                $start = $date->copy()->subMonth()->startOfMonth();
                $end = $date->copy()->subMonth()->endOfMonth();
                break;
            case Merchant::SETTLE_HALF_YEARLY:
                if($date->month > 6){
                    $start = $date->copy()->startOfYear();
                    $end = $date->copy()->startOfYear()->addMonths(5)->endOfMonth();
                }else {
                    $start = $date->copy()->subYear()->startOfYear()->addMonths(6)->startOfMonth();
                    $end = $date->copy()->subYear()->endOfYear();
                }
                break;
            case Merchant::SETTLE_YEARLY:
                $start = $date->copy()->subYear()->startOfYear();
                $end = $date->copy()->subYear()->endOfYear();
                break;
            default :
                throw new \Exception('错误的结算方式:' . $this->settlementCycleType);
        }
        // 查询周结的商家列表
        Merchant::where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($start, $end){
                $merchants->each(function ($item) use ($start, $end){
                    SettlementForMerchant::dispatch($item->id, $start, $end);
                });
            });

        Log::info('结算任务执行完成');
        //
    }
}
