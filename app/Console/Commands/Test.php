<?php

namespace App\Console\Commands;

use App\Jobs\SettlementForMerchant;
use App\Modules\Merchant\Merchant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
//        SettlementJob::dispatch(Merchant::SETTLE_WEEKLY);
        $this->remedySettlementsOf20180521and20180528();
    }

    private function remedySettlementsOf20180521and20180528()
    {
        // 执行 20180521 的结算
        $date = '2018-05-21';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        Merchant::where('settlement_cycle_type', Merchant::SETTLE_WEEKLY)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($start, $end){
                $merchants->each(function ($item) use ($start, $end){
                    SettlementForMerchant::dispatch($item->id, $start, $end);
                });
            });
        // 执行 20180528 的结算
        $date = '2018-05-28';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        Merchant::where('settlement_cycle_type', Merchant::SETTLE_WEEKLY)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($start, $end){
                $merchants->each(function ($item) use ($start, $end){
                    SettlementForMerchant::dispatch($item->id, $start, $end);
                });
            });

    }
}
