<?php

namespace App\Jobs\Schedule;

use App\Jobs\OrderFinishedJob;
use App\Modules\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 超市订单超过7天自动收货
 * Class OrderAutoFinished
 * @package App\Jobs
 */
class OrderAutoConfirmed implements ShouldQueue
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
        Log::info('开始执行自动收货');
        Order::where('type', Order::TYPE_SUPERMARKET)
            ->where('deliver_time', '<', Carbon::now()->subDay(7))
            ->where('status', Order::STATUS_DELIVERED)
            ->chunk(1000, function(Collection $list){
                foreach ($list as $l) {
                    $l->take_delivery_time =  Carbon::now();
                    $l->finish_time = Carbon::now();
                    $l->status = Order::STATUS_FINISHED;
                    $l->save();
                    OrderFinishedJob::dispatch($l)->onQueue('order:finished')->delay(now()->addSecond(5));
                }
            });

        Log::info('自动收货执行完成');
    }
}
