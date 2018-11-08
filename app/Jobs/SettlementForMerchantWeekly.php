<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

use App\Modules\Merchant\Merchant;
use App\Modules\Settlement\SettlementPlatformService;

/**
 * Author: Jerry
 * Date:    180823
 * 处理商家每月结算
 */
class SettlementForMerchantWeekly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;
    protected $date;

    /**
     *
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param    int $merchantId
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
    /**
     * 周结算商家到平台的订单
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @return   void
     * @throws \Exception
     */
    public function handle()
    {
        $merchant = Merchant::findOrFail($this->merchantId);

        try {
            SettlementPlatformService::settlementWeekly($merchant, $this->date);
        }catch (\Exception $e){
            Log::error('该商家上周结算错误, 错误原因:' . $e->getMessage(), [
                'merchantId' => $this->merchantId,
                'date' => $this->date,
                'timestamp' => date('Y-m-d H:i:s'),
                'exception' => $e,
            ]);
        }
    }
}
