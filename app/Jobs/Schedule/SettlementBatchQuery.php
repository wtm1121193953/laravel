<?php
/**
 * 结算单生成批次
 */
namespace App\Jobs\Schedule;

use App\Modules\Settlement\SettlementPlatformKuaiQianBatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SettlementBatchQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //
        Log::info('快钱批次查询 :Start');

        SettlementPlatformKuaiQianBatchService::batchQuery();

        Log::info('快钱批次查询 :end');
    }
}
