<?php

namespace App\Console;

use App\Jobs\Schedule\AutoDownGoodsJob;
use App\Jobs\Schedule\InviteUserStatisticsDailyJob;
use App\Jobs\Schedule\OrderAutoFinished;
use App\Jobs\Schedule\OrderExpired;
use App\Jobs\Schedule\SettlementDaily;
use App\Jobs\Schedule\SettlementWeekly;
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
        /** 结算任务 */
        // 周结, 旧结算逻辑, 支付到运营中心的订单结算
        $schedule->job(new SettlementWeekly(Merchant::SETTLE_WEEKLY))
            ->weeklyOn(1);
        // 半月结
        $schedule->job(new SettlementDaily)->daily();
        // T+1结算统计 Author：Jerry Date：180824
        $schedule->job(new SettlementForMerchantDaily)->daily();
        /**团购商品过期自动下架*/
        $schedule->job(AutoDownGoodsJob::class)->daily();
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
