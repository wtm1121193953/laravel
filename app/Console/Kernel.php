<?php

namespace App\Console;

use App\Jobs\Schedule\AutoDownGoodsJob;
use App\Jobs\Schedule\InviteUserStatisticsDailyJob;
use App\Jobs\Schedule\OrderAutoConfirmed;
use App\Jobs\Schedule\OrderAutoFinished;
use App\Jobs\Schedule\OrderExpired;
use App\Jobs\Schedule\PlatformTradeRecordsDailyJob;
use App\Jobs\Schedule\SettlementBatchQuery;
use App\Jobs\Schedule\SettlementDaily;
use App\Jobs\Schedule\SettlementForPlatformWeekly;
use App\Jobs\Schedule\SettlementGenBatch;
use App\Jobs\Schedule\SettlementWeekly;
use App\Modules\Merchant\Merchant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;
use App\Jobs\Schedule\OperAndMerchantAndUserStatisticsDailyJob;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        /** 用户邀请记录每日统计, 每日统计昨日的注册数 */
        $schedule->job(new InviteUserStatisticsDailyJob((new Carbon())->subDay()))->daily();
        /** 订单超时自动关闭 */
        $schedule->job(OrderExpired::class)->hourly();
        /** 输入金额付款订单自动完成 */
        $schedule->job(OrderAutoFinished::class)->daily();
        /**超过7天自动收货任务*/
        $schedule->job(OrderAutoConfirmed::class)->hourly();
        /** 结算任务 */
        // 周结, 旧结算逻辑, 支付到运营中心的订单结算
        $schedule->job(new SettlementWeekly(Merchant::SETTLE_WEEKLY))
            ->weeklyOn(1);
        //周结, 新结算逻辑, 支付到平台的订单结算
        $schedule->job( new SettlementForPlatformWeekly() )->weeklyOn(1);
        // T+1结算统计 Author：Jerry Date：180824
        $schedule->job( new SettlementDaily() )->daily();
        //T+1自动打款 批次生成每天早8点
        $schedule->job( new SettlementGenBatch() )->at('08:00');
        //自动打款批次查询
        $schedule->job( new SettlementBatchQuery())->hourly();
        // T+1结算分账任务， 生成的结算单每天8点自动打款
        // 运营中心营销当日统计
        $schedule->job( new OperAndMerchantAndUserStatisticsDailyJob((new Carbon())->subDay()->endOfDay()->format('Y-m-d H:i:s')))->daily();
        //$schedule->job(new SettlementAgentPayDaily())->dailyAt('08:00');
        /**团购商品过期自动下架*/
        $schedule->job(AutoDownGoodsJob::class)->daily();
        //平台交易汇总
        $schedule->job( new PlatformTradeRecordsDailyJob((new Carbon())->subDay()->endOfDay()->format('Y-m-d H:i:s')))->daily();
        //平台交易汇总 (每1分钟执行)
        $schedule->job( new PlatformTradeRecordsDailyJob(Carbon::now()->endOfDay()->format('Y-m-d H:i:s')))->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
