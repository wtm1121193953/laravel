<?php

namespace App\Jobs\Schedule;

use App\Modules\Merchant\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\SettlementForMerchantWeekly;
use App\Modules\Oper\Oper;

/**
 * 每周订单结算, 新结算逻辑, 支付到平台时使用此结算逻辑
 * Class SettlementForPlatformWeekly
 * @package App\Jobs\Schedule
 */
class SettlementForPlatformWeekly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param null $date
     */
    public function __construct($date=null )
    {
        $date = $date ?? Carbon::now();
        $this->date = $date->copy()->subWeek()->endOfWeek();
    }


    /**
     * 每周结算
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @return   void
     */
    public function handle()
    {
        //计算上周要结算的任务
        Log::info('开始执行每周结算任务');
        $date   = $this->date;
        // 获取运营中心支付到平台(平台参与分成) 商家
        Merchant::where('settlement_cycle_type',Merchant::SETTLE_WEEKLY)
            ->whereHas('oper', function($query){
            $query->whereIn('pay_to_platform', [ Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING ]);
        })
            ->select('id')
            ->chunk(100, function( $merchants ) use ( $date ) {
            $merchants->each( function( $item ) use ( $date) {
                SettlementForMerchantWeekly::dispatch( $item->id, $date );
            });
        });
        Log::info('每周结算任务完成');
    }
}
