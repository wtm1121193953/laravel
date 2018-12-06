<?php

namespace App\Jobs\Schedule;

use App\Modules\CsStatistics\CsStatisticsMerchantOrderService;
use App\Modules\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 超市商户最近30天订单统计
 * Class OrderAutoFinished
 * @package App\Jobs
 */
class CsMerchantOrder implements ShouldQueue
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
        $start_date = date('Y-m-d',time()-30*86400);
        Log::info('超市商户最近30天订单统计'.$start_date);

        $status = [
            Order::STATUS_FINISHED,
            Order::STATUS_REFUNDED,
            Order::STATUS_PAID,
            Order::STATUS_UNDELIVERED,
            Order::STATUS_DELIVERED,
            Order::STATUS_NOT_TAKE_BY_SELF
        ];
        $status = implode(',',$status);

        $sql = "SELECT merchant_id,count(*) c from orders where merchant_type=2 and created_at>'{$start_date}' and `status` in ({$status})
GROUP BY merchant_id
        ";

        $rs = DB::select($sql);
        if ($rs) {
            foreach ($rs as $v) {
                $row = CsStatisticsMerchantOrderService::createMerchantData($v->merchant_id);
                $row->order_number_today = 0;
                $row->order_number_30d = $v->c;
                $row->save();
            }
        }

        Log::info('超市商户最近30天订单统计结束');
    }
}
