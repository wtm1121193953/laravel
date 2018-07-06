<?php

namespace App\Console;

use App\Jobs\InviteUserStatisticsDailyJob;
use App\Jobs\OrderAutoFinished;
use App\Jobs\OrderExpired;
use App\Jobs\SettlementJob;
use App\Modules\Merchant\Merchant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        \App\Console\Commands\Test::class,
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
        $schedule->command('test')->everyMinute();
        /** 用户邀请记录每日统计, 每日统计昨日的注册数 */
        $schedule->job(new InviteUserStatisticsDailyJob((new Carbon())->subDay()))->daily();
        /** 订单超时自动关闭 */
        $schedule->job(OrderExpired::class)->hourly();
        /** 输入金额付款订单自动完成 */
        $schedule->job(OrderAutoFinished::class)->daily();
        /** 结算任务 */
        // 周结
        $schedule->job(new SettlementJob(Merchant::SETTLE_WEEKLY))
            ->weeklyOn(1);
        // 半月结
        $schedule->job(new SettlementJob(Merchant::SETTLE_HALF_MONTHLY))
            ->monthlyOn(1);
        $schedule->job(new SettlementJob(Merchant::SETTLE_HALF_MONTHLY))
            ->monthlyOn(16);
        // 月结
        $schedule->job(new SettlementJob(Merchant::SETTLE_MONTHLY))
            ->monthly();
        // 半年结
        $schedule->job(new SettlementJob(Merchant::SETTLE_HALF_YEARLY))
            ->cron('0 0 1 1,7 *');
        // 年结
        $schedule->job(new SettlementJob(Merchant::SETTLE_YEARLY))
            ->yearly();
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
