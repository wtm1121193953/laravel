<?php

namespace App\Console\Commands;

use App\Jobs\OrderPaidJob;
use App\Modules\Order\Order;
use App\Modules\UserCredit\UserCreditRecord;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CreditComputeHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credit:compute {orderNo} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算历史积分, 该命令会将所有历史订单找出并计算用户应获得的积分(已计算过的不重复计算)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        Order::chunk(100, function(Collection $list){
            $list->each(function (Order $item){
                // 查询该订单是否已计算过积分
                $creditRecord = UserCreditRecord::where('order_no', $item->order_no)
                    ->where('type', UserCreditRecord::TYPE_FROM_SELF)
                    ->first();
                if(empty($creditRecord)){
                    // 如果没有计算过积分, 才计算积分
                    OrderPaidJob::dispatch($item);
                }
            });
        });
    }
}
