<?php

namespace App\Jobs\Schedule;

use App\Modules\Goods\Goods;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoDownGoodsJob implements ShouldQueue
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
     * 团购商品 过期 自动下架
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('开始执行过期商品自动下架任务');
        Goods::where('status', Goods::STATUS_ON)
            ->where('end_date', '<', Carbon::today())
            ->update(['status' => Goods::STATUS_OFF]);
        Log::info('过期商品自动下架任务完成');
    }
}
