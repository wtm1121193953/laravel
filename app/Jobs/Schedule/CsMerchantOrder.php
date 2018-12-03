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
        $end_date = date('Y-m-d');
        Log::info('超市商户最近30天订单统计'.$start_date.'到' . $end_date);

        $start_date = date('Y-m-d',time()-30*86400);
        $end_date = date('Y-m-d');

        $status = [Order::STATUS_FINISHED];
        $status = implode(',',$status);

        $sql = "SELECT merchant_id,count(*) c from orders where created_at<'$end_date' and created_at>'{$start_date}' and merchant_type=2 and `status` in ({$status})
GROUP BY merchant_id
        ";

        $rs = DB::select($sql);
        if ($rs) {
            foreach ($rs as $v) {
                $row = CsStatisticsMerchantOrderService::createMerchantData($v->merchant_id);
                $row->order_number_30d = $v->c;
                $row->save();
            }
        }


        Log::info('超市商户最近30天订单统计结束');
    }
}
