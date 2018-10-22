<?php

namespace App\Jobs\Schedule;

use App\Modules\Platform\PlatformTradeRecordService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PlatformTradeRecordsDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $date = '';

    public function __construct($date='')
    {
        //
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $this->date = $date;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        PlatformTradeRecordService::daily($this->date);
    }
}