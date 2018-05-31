<?php

namespace App\Console\Commands;

use App\Jobs\OrderPaidJob;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        OrderPaidJob::dispatch(Order::where('status', 4)->first());
        dd();
//        SettlementJob::dispatch(Merchant::SETTLE_WEEKLY);
        $this->remedySettlementsOf20180521and20180528();
    }

    private function remedySettlementsOf20180521and20180528()
    {
        // 执行 20180521 的结算
        $date = '2018-05-21';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        $this->settlementForDate($date, $start, $end);
        // 执行 20180528 的结算
        $date = '2018-05-28';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        $this->settlementForDate($date, $start, $end);
    }

    private function settlementForDate($date, $start, $end)
    {
        Merchant::where('settlement_cycle_type', Merchant::SETTLE_WEEKLY)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($date, $start, $end){
                $merchants->each(function ($item) use ($date, $start, $end){

                    $merchant = $item;
                    // 判断该周期是否已结算过, 若结算过则不再重复结算
                    $settlement = Settlement::where('merchant_id', $merchant->id)
                        ->where('settlement_date', $date)
                        ->first();
                    if($settlement){
                        Log::info('该周期已结算,跳过结算', [
                            'merchantId' => $merchant->id,
                            'date' => $date,
                            'start' => $start,
                            'end' => $end,
                        ]);
                        return ;
                    }
                    // 先生成结算单, 以便于结算时在订单中保存结算信息
                    $settlement = new Settlement();
                    $settlement->oper_id = $merchant->oper_id;
                    $settlement->merchant_id = $merchant->id;
                    $settlement->settlement_date = $date;
                    $settlement->start_date = $start;
                    $settlement->end_date = $end;
                    $settlement->settlement_cycle_type = $merchant->settlement_cycle_type;
                    $settlement->settlement_rate = $merchant->settlement_rate;
                    $settlement->bank_open_name = $merchant->bank_open_name;
                    $settlement->bank_card_no = $merchant->bank_card_no;
                    $settlement->sub_bank_name = $merchant->sub_bank_name;
                    $settlement->bank_open_address = $merchant->bank_open_address;
                    $settlement->invoice_title = $merchant->invoice_title;
                    $settlement->invoice_no = $merchant->invoice_no;
                    $settlement->amount = 0;
                    $settlement->charge_amount = 0;
                    $settlement->real_amount = 0;
                    $settlement->save();

                    // 查询商家结算周期内的所有订单并统计
                    Order::where('merchant_id', $merchant->id)
                        ->where('status', Order::STATUS_FINISHED)
                        ->whereBetween('finish_time', [$start, $end])
                        ->chunk(1000, function (Collection $orders) use ($merchant, $settlement){
                            $orders->each(function($item) use ($merchant, $settlement){

                                $settlement->amount += $item->pay_price;

                                $item->settlement_status = 2;
                                $item->settlement_id = $settlement->id;
                                $item->save();
                            });
                        });

                    $settlement->charge_amount = $settlement->amount * 1.0 * $settlement->settlement_rate / 100;
                    $settlement->real_amount = $settlement->amount - $settlement->charge_amount;
                    $settlement->save();
                });
            });
    }
}
