<?php
/**
 * 结算单生成批次
 */
namespace App\Jobs\Schedule;

use App\Modules\Settlement\SettlementPlatformService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SettlementGenBatch implements ShouldQueue
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
     *
     * @return void
     */
    public function handle()
    {
        //
        Log::info('结算单生成批次，块钱 :Start');

        SettlementPlatformService::genBatch();

        Log::info('结算单生成批次，块钱 :end');
    }
}
