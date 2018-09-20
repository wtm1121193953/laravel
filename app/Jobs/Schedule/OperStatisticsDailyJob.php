<?php

namespace App\Jobs\Schedule;

use App\Modules\Oper\OperStatistics;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Oper\Oper;

/**
 * 用于每日新增运营中心营销统计
 * Class OperStatisticsDailyJob
 * Author:   JerryChan
 * Date:     2018/9/20 16:46
 * @package App\Jobs\Schedule
 */
class OperStatisticsDailyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        Log::info('生成运营中心营销统计数据 :Start');
        $date = date('Y-m-d');
        // 获取operList数据
        $operList = Oper::whereStatus(Oper::STATUS_NORMAL)->get(['id'])->toArray();
        $saveList = [];
        foreach ($operList as $k => $v) {

            $saveList[] = [
                'date' => $date,
                'oper_id' => $v['id'],
                'merchant_num' => 0,
                'user_num' => 0,
                'order_paid_num' => 0,
                'order_refund_num' => 0,
                'order_paid_amount' => 0,
                'order_refund_amount' => 0,
            ];
        }
        $operStatistics = new OperStatistics();
        DB::table($operStatistics->getTable())->insert($saveList);
        Log::info('生成运营中心营销统计数据 :end');
    }
}
