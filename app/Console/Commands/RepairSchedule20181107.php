<?php

namespace App\Console\Commands;

use App\Jobs\ConsumeQuotaUnfreezeJob;
use App\Jobs\FeeSplittingUnfreezeJob;
use App\Jobs\OrderFinishedJob;
use App\Jobs\Schedule\AutoDownGoodsJob;
use App\Jobs\Schedule\InviteUserStatisticsDailyJob;
use App\Jobs\Schedule\OperStatisticsDailyJob;
use App\Jobs\Schedule\PlatformTradeRecordsDailyJob;
use App\Jobs\Schedule\SettlementDaily;
use App\Jobs\Schedule\SettlementWeekly;
use App\Jobs\SettlementForMerchant;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletConsumeQuotaUnfreezeRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RepairSchedule20181107 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:schedule20181107';

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
        $list = Order::where('status', 7)
            ->where('splitting_status', 1)
            ->where('pay_target_type', 2)
            ->whereHas('oper', function($query){
                $query->where('pay_to_platform', 2);
            })
            ->get();
        $list->each(function($item) {
            OrderFinishedJob::dispatch($item);
        });
//        $list = FeeSplittingRecord::where('status', 1)->get();
//        $list->each(function($item){
//            FeeSplittingUnfreezeJob::dispatch(Order::find($item->order_id));
//        });
//        $list = WalletConsumeQuotaRecord::where('status', 1)->get();
//        $list->each(function ($item) {
//            ConsumeQuotaUnfreezeJob::dispatch(Order::find($item->order_id));
//        });
//        SettlementDaily::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-08')->subDay());
//        PlatformTradeRecordsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-08')->subDay()->endOfDay()->format('Y-m-d H:i:s'));
//        InviteUserStatisticsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-08')->subDay());
//        OperStatisticsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-08')->subDay()->endOfDay()->format('Y-m-d H:i:s'));
//        AutoDownGoodsJob::dispatch();

//        SettlementWeekly::dispatch(Merchant::SETTLE_WEEKLY, Carbon::createFromFormat('Y-m-d', '2018-11-07'));

        /*$date = Carbon::createFromFormat('Y-m-d', '2018-11-07');
        $start = $date->copy()->subWeek()->startOfWeek();
        $end = $date->copy()->subWeek()->endOfWeek();
        Merchant::where('settlement_cycle_type', '<>', 1)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($start, $end){
                $merchants->each(function ($item) use ($start, $end){
                    SettlementForMerchant::dispatch($item->id, $start, $end);
                });
            });*/


//        $order = Order::where('order_no', 'O20181107085947617449')->first();
//        FeeSplittingUnfreezeJob::dispatch($order)/*->delay(Carbon::now()->addDay(1))*/;
//        ConsumeQuotaUnfreezeJob::dispatch($order)/*->delay(Carbon::now()->addDay(1))*/;

//        InviteUserStatisticsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-07')->subDay());

    }
}
