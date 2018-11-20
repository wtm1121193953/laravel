<?php

namespace App\Jobs\Cs;

use App\Modules\Cs\CsMerchant;
use App\Modules\Settlement\SettlementPlatformService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Author:  Jerry
 * 处理超市商家买家结算
 * Class SettlementForCsMerchantDaily
 * @package App\Jobs\Cs
 */
class SettlementForCsMerchantDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;
    protected $date;

    /**
     * SettlementForCsMerchantDaily constructor.
     * @param $merchantId
     * @param Carbon $date
     */
    public function __construct($merchantId, Carbon $date)
    {
        $this->merchantId = $merchantId;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $supermarket = CsMerchant::findOrFail($this->merchantId);
        try {
            SettlementPlatformService::settlement($supermarket, $this->date);
        }catch (\Exception $e){
            Log::error('该超市每日结算错误, 错误原因:' . $e->getMessage(), [
                'merchantId' => $this->merchantId,
                'date' => $this->date,
                'timestamp' => date('Y-m-d H:i:s'),
                'exception' => $e,
            ]);
        }
    }
}
