<?php

namespace App\Jobs;

use App\Modules\User\UserStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UserStatisticsByUserId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $startTime;
    protected $endTime;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $startTime
     * @param $endTime
     */
    public function __construct($userId, $startTime, $endTime)
    {
        $this->userId = $userId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        UserStatisticsService::statisticsByUserId($this->userId, $this->startTime, $this->endTime);
    }
}
