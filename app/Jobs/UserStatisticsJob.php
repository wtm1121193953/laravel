<?php

namespace App\Jobs;

use App\Modules\User\UserStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UserStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $endTime;

    /**
     * Create a new job instance.
     *
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
     *
     * @return void
     */
    public function handle()
    {

        Log::info('生成 用户营销统计数据 :Start');

        UserStatisticsService::statistics($this->endTime);

        Log::info('生成 用户营销统计数据 :end');
    }
}
