<?php

namespace App\Jobs;

use App\Modules\Merchant\MerchantStatisticsService;
use App\Modules\User\UserStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MerchantStatisticsByMerchantId implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;
    protected $startTime;
    protected $endTime;

    /**
     * Create a new job instance.
     *
     * @param $merchantId
     * @param $startTime
     * @param $endTime
     */
    public function __construct($merchantId, $startTime, $endTime)
    {
        $this->merchantId = $merchantId;
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
        MerchantStatisticsService::statisticsByMerchantId($this->merchantId, $this->startTime, $this->endTime);
    }
}
