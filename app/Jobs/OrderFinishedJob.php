<?php

namespace App\Jobs;

use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class OrderFinishedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        //
        if ($order->status != Order::STATUS_FINISHED) {
            Log::info('订单号：'.$order->order_no. ', 状态：'.$order->status. ', 不能进行分润！');
            return;
        } elseif ($order->splitting_status == Order::SPLITTING_STATUS_YES) {
            Log::info('订单号: ' . $order->order_no . ' 已分润, 不再重复计算');
            return;
        }
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        // 1. 执行分润
        $this->feeSplitting();
        // 2. 处理消费额 消费额逻辑暂时去掉, 需要修改
        $this->consumeQuota();
        // 延迟24小时分发解冻积分以及消费额操作
//        FeeSplittingUnfreezeJob::dispatch()->delay();
//        ConsumeQuotaUnfreezeJob::dispatch()->delay();
    }

    /**
     * 处理分润
     */
    private function feeSplitting()
    {
        FeeSplittingService::feeSplittingByOrder($this->order);
    }

    /**
     * 处理消费额
     */
    private function consumeQuota()
    {

    }

}
