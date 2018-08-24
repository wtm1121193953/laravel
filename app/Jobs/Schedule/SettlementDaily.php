<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\SettlementForMerchantDaily;
use App\Modules\Oper\Oper;

/**
 * 每日订单结算, 新结算逻辑, 支付到平台时使用此结算逻辑
 * Class SettlementDaily
 * @package App\Jobs\Schedule
 */
class SettlementDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     * @Author   Jerry
     * @DateTime 2018-08-23
     */
    public function __construct()
    {
        $this->date = Carbon::yesterday();
    }


    /**
     * 每天结算
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @return   void
     */
    public function handle()
    {
        //计算昨天要结算的任务
        Log::info('开始执行每日结算任务');
        $date   = $this->date;
        $start  = $date->startOfDay();
        $end    = $date->endOfDay();
        // 获取运营中心支付到平台(平台参与分成) 商家
        DB::table('merchants')
            ->join('opers', 'merchants.oper_id', '=', 'opers.id')
            ->where('opers.pay_to_platform', Oper::PAY_TO_PLATFORM_YES2)
            ->select('merchant.id')
            ->chunk(100, function( $merchants ) use ( $start, $end ) {
                $merchants->each( function( $item ) use ( $start, $end ) {
                    SettlementForMerchantDaily::dispatch( $item->id, $start, $end );
                });
            });
        Log::info('每日结算任务完成');
    }
}
