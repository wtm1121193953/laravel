<?php

namespace App\Jobs;

use App\Modules\Invite\InviteStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InviteStatisticsDailyUpdateByDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @param $date
     */
    public function __construct($date)
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
        //
        InviteStatisticsService::batchUpdateDailyStatisticsByDate($this->date);
    }
}
