<?php

namespace App\Jobs;

use App\Modules\Tps\TpsBind;
use App\Support\TpsApi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class TpsBindsUpdateTpsUidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tpsBind;

    /**
     * Create a new job instance.
     *
     * @param TpsBind $tpsBind
     */
    public function __construct(TpsBind $tpsBind)
    {
        //
        $this->tpsBind = $tpsBind;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Log::info('更新TPS uid');
        $result = TpsApi::getUserInfo($this->tpsBind->tps_account);
        if (!empty($result['data']['uid'])) {
            $this->tpsBind->tps_uid = $result['data']['uid'];
            $this->tpsBind->save();
        }
    }
}
