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
use App\Modules\Merchant\SettlementPlatform;
use App\Modules\Merchant\SettlementPlatformService;
/**
 * Author: Jerry
 * Date:    180823
 * 处理商家每日结算
 */
class SettlementForMerchantDaily implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;
    protected $start;
    protected $end;

    /**
     *
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param    int    $merchantId
     * @param    Carbon $start
     * @param    Carbon $end
     * @return void
     */
    public function __construct($merchantId, Carbon $start, Carbon $end)
    {
        $this->merchantId   = $merchantId;
        $this->start        = $start;
        $this->end          = $end;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    /**
     * 新结算指定日期商家到平台的订单
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @return   void
     */
    public function handle()
    {
        $merchant   = Merchant::findOrFail( $this->merchantId );
        // 判断该店是否已结算
        $exist      = SettlementPlatform::where('merchant_id', $this->merchantId );
        if( $exist )
        {
            Log::info('该每日结算已结算,跳过结算', [
                'merchantId' => $this->merchantId,
                'date' => Carbon::now()->format('Y-m-d'),
                'start' => $this->start,
                'end' => $this->end,
            ]);
            return ;
        }
        $res = SettlementPlatformService::settlement( $merchant, $this->start, $this->end);
        if( !$res )  Log::info('该商家每日结算错误，商家id：'.$this->merchantId);
    }
}
