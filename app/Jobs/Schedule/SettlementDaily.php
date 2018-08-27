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
     * @param null $date
     */
    public function __construct(  $date=null )
    {
        $date = $date ?? Carbon::yesterday();
        $this->date = $date;
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
        // 获取运营中心支付到平台(平台参与分成) 商家
        Merchant::whereHas('oper', function($query){
            $query->whereIn('pay_to_platform', [ Oper::PAY_TO_PLATFORM_YES, Oper::PAY_TO_PLATFORM_YES2 ]);
        })
            ->select('id')
            ->chunk(100, function( $merchants ) use ( $date ) {
            $merchants->each( function( $item ) use ( $date) {
                SettlementForMerchantDaily::dispatch( $item->id, $date );
            });
        });/*
        DB::table('merchants')
            ->join('opers', 'merchants.oper_id', '=', 'opers.id')
            ->whereIn('opers.pay_to_platform', [ Oper::PAY_TO_PLATFORM_YES, Oper::PAY_TO_PLATFORM_YES2 ])
            ->select('merchants.id')
            ->chunk(100, function( $merchants ) use ( $date ) {
                $merchants->each( function( $item ) use ( $date) {
                    SettlementForMerchantDaily::dispatch( $item->id, $date );
                });
            });*/
        Log::info('每日结算任务完成');
    }
}
