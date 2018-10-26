<?php

namespace App\Jobs;

use App\Modules\Invite\InviteStatisticsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InviteStatisticsDailyUpdateByOriginInfoAndDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originId;
    protected $originType;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @param $originId
     * @param $originType
     * @param Carbon $date
     */
    public function __construct($originId, $originType, Carbon $date)
    {
        $this->originId = $originId;
        $this->originType = $originType;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        InviteStatisticsService::updateDailyStatByOriginInfoAndDate($this->originId, $this->originType, $this->date);
    }
}
