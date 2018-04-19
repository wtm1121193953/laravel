<?php

namespace App\Console;

use App\Jobs\OrderExpired;
use App\Jobs\SettlementHalfMonthly;
use App\Jobs\SettlementHalfYearly;
use App\Jobs\SettlementMonthly;
use App\Jobs\SettlementJob;
use App\Jobs\SettlementYearly;
use App\Modules\Merchant\Merchant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        /**
         * 订单超时自动关闭
         */
        $schedule->job(OrderExpired::class)->hourly();
        /**
         * 结算任务
         */
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
