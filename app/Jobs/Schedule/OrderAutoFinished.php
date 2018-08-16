<?php

namespace App\Jobs\Schedule;

use App\Modules\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 订单自动完成任务(仅直接输入金额付款的订单, 付款超过24小时后自动设置状态为已完成)
 * Class OrderAutoFinished
 * @package App\Jobs
 */
class OrderAutoFinished implements ShouldQueue
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
        Log::info('开始执行输入金额付款订单自动完成定时任务');
        Order::where('type', Order::TYPE_SCAN_QRCODE_PAY)
            ->where('pay_time', '<', Carbon::yesterday())
            ->where('status', Order::STATUS_PAID)
            ->update(['status' => Order::STATUS_FINISHED, 'finish_time' => Carbon::now()]);
        Log::info('输入金额付款订单自动完成定时任务执行完成');
    }
}
