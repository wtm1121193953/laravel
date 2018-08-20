<?php

namespace App\Jobs\Schedule;

use App\Modules\Invite\InviteStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;

/**
 * 邀请用户每日统计定时任务
 * Class InviteUserStatisticsDailyJob
 * @package App\Jobs
 */
class InviteUserStatisticsDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @param Carbon $date
     */
    public function __construct(Carbon $date)
    {
        //
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 执行每日数据统计
        InviteStatisticsService::batchUpdateDailyStatisticsByDate($this->date);
    }
}
