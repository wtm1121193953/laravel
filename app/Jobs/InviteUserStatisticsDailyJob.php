<?php

namespace App\Jobs;

use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserStatisticsDaily;
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
     * @return void
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
        // 查询出每个origin对应当天的邀请数量并存储到每日统计表中
        $list = InviteUserRecord::whereDate('created_at', $this->date->format('Y-m-d'))
            ->groupBy('origin_id', 'origin_type')
            ->select('origin_id', 'origin_type')
            ->selectRaw('count(1) as total')
            ->get();
        $list->each(function($item){
            $statDaily = new InviteUserStatisticsDaily();
            $statDaily->date = $this->date;
            $statDaily->origin_id = $item->origin_id;
            $statDaily->origin_type = $item->origin_type;
            $statDaily->invite_count = $item->total;
            $statDaily->save();
        });
    }
}
