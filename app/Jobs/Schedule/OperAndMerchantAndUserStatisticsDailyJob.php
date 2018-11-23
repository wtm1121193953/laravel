<?php

namespace App\Jobs\Schedule;

use App\Jobs\MerchantStatisticsJob;
use App\Jobs\OperStatisticsJob;
use App\Jobs\UserStatisticsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * 用于每日新增 营销统计
 * Class OperStatisticsDailyJob
 * Author:   JerryChan
 * Date:     2018/9/20 16:46
 * @package App\Jobs\Schedule
 */
class OperAndMerchantAndUserStatisticsDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $endTime = '';

    /**
     * Create a new job instance.
     * @param string $endTime
     */
    public function __construct($endTime = '')
    {
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }
        $this->endTime = $endTime;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        Log::info('生成 营销统计数据 :Start');

        MerchantStatisticsJob::dispatch($this->endTime);
        OperStatisticsJob::dispatch($this->endTime);
        UserStatisticsJob::dispatch($this->endTime);

        Log::info('生成 营销统计数据 :end');
    }
}
