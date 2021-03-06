<?php

namespace App\Jobs;

use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Order\Order;
use App\Modules\Wallet\ConsumeQuotaService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 订单完成任务, 此任务需要放入 order:finished 队列中, 只开启一个监听,以防止并发执行时出现某些不可预料的错误
 * Class OrderFinishedJob
 * @package App\Jobs
 */
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
        // 任务逻辑...
        $order = $this->order;
        if ($order->status != Order::STATUS_FINISHED) {
            Log::info('订单号：'.$order->order_no. ', 状态：'.$order->status. ', 不能进行分润！');
            return;
        } elseif ($order->splitting_status == Order::SPLITTING_STATUS_YES) {
            Log::info('订单号: ' . $order->order_no . ' 已分润, 不再重复计算');
            return;
        }

        DB::beginTransaction();
        try{
            // 1. 执行分润
            $this->feeSplitting();
            // 2. 处理消费额 消费额逻辑暂时去掉, 需要修改
            $this->consumeQuota();

            DB::commit();

            // 延迟24小时分发解冻分润以及消费额操作
            FeeSplittingUnfreezeJob::dispatch($this->order)/*->delay(Carbon::now()->addDay(1))*/;
            ConsumeQuotaUnfreezeJob::dispatch($this->order)/*->delay(Carbon::now()->addDay(1))*/;
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 处理分润
     * @throws \Exception
     */
    private function feeSplitting()
    {
        FeeSplittingService::feeSplittingByOrder($this->order);
    }

    /**
     * 处理消费额
     * @throws \Exception
     */
    private function consumeQuota()
    {
        ConsumeQuotaService::addFreezeConsumeQuota($this->order);
    }

}
