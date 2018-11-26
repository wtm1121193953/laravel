<?php

namespace App\Jobs\Cs;

use App\Jobs\OrderFinishedJob;
use App\Modules\Order\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class DeliveredOrderAutoFinishedJob implements ShouldQueue
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
     */
    public function handle()
    {
        $order = $this->order;
        if ($order->merchant_type == Order::MERCHANT_TYPE_SUPERMARKET && $order->status == Order::STATUS_DELIVERED) {
            $order->status = Order::STATUS_FINISHED;
            $order->finish_time = Carbon::now();
            $order->save();
            OrderFinishedJob::dispatch($order);
        } else {
            Log::info('超市订单自动完成 错误', [
                'status' => $order->status,
                'merchant_type' => $order->merchant_type,
                'order' => $order,
            ]);
        }
    }
}
