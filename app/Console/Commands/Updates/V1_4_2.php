<?php

namespace App\Console\Commands\Updates;

use App\Modules\Order\OrderRefund;
use App\Modules\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class V1_4_2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // 历史订单分润
        // 历史订单消费额转换
        // 补充退款中的退款单号
        OrderRefund::chunk(1000, function(Collection $list){
            $list->each(function (OrderRefund $item){
                $item->refund_no = 'R' . $item->created_at->format('YmdHis') . rand(1000, 9999);
            });
        });
    }
}
