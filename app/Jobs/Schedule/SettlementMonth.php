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
use App\Jobs\SettlementForMerchantMonth;
use App\Modules\Oper\Oper;

/**
 * 每月定时结算上月之前微信支付的订单, 新结算逻辑, 支付到平台时使用此结算逻辑
 * Class SettlementMonth
 * @package App\Jobs\Schedule
 */
class SettlementMonth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param null $date
     */
    public function __construct(  $date=null )
    {
        $date = $date ?? Carbon::now();

        $this->date = $date->subMonth()->endOfMonth();
    }


    /**
     * 每天结算
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @return   void
     */
    public function handle()
    {

        //计算上月要结算的任务
        Log::info('开始执行上月结算任务');
        $date = $this->date;
        // 获取运营中心支付到平台(平台参与分成) 商家
        Merchant::where('settlement_cycle_type',Merchant::SETTLE_DAILY_AUTO)
            ->whereHas('oper', function($query){
            $query->whereIn('pay_to_platform', [ Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING ]);
        })
            ->select('id')
            ->chunk(100, function( $merchants ) use ( $date ) {
            $merchants->each( function( $item ) use ( $date) {
                SettlementForMerchantMonth::dispatch( $item->id, $date );
            });
        });
        Log::info('每日结算任务完成');
    }
}
