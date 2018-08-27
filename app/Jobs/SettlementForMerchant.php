<?php

namespace App\Jobs;

use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SettlementForMerchant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;
    protected $start;
    protected $end;

    /**
     * Create a new job instance.
     *
     * @param int $merchantId
     * @param Carbon $start
     * @param Carbon $end
     */
    public function __construct($merchantId, Carbon $start, Carbon $end)
    {
        //
        $this->merchantId = $merchantId;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Execute the job.
     * 结算指定商家指定日期的订单
     * @return void
     */
    public function handle()
    {
        $merchant = Merchant::findOrFail($this->merchantId);
        // 判断该周期是否已结算过, 若结算过则不再重复结算
        $settlement = Settlement::where('merchant_id', $this->merchantId)
            ->where('start_date', $this->start->format('Y-m-d'))
            ->where('end_date', $this->end->format('Y-m-d'))
            ->first();
        if($settlement){
            Log::info('该周期已结算,跳过结算', [
                'merchantId' => $this->merchantId,
                'date' => Carbon::now()->format('Y-m-d'),
                'start' => $this->start,
                'end' => $this->end,
            ]);
            return ;
        }
        // 先生成结算单, 以便于结算时在订单中保存结算信息
        $settlement = new Settlement();
        $settlement->oper_id = $merchant->oper_id;
        $settlement->merchant_id = $this->merchantId;
        $settlement->settlement_date = Carbon::now();
        $settlement->start_date = $this->start;
        $settlement->end_date = $this->end;
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
        Order::where('merchant_id', $this->merchantId)
            ->where('settlement_status', 1)
            ->where('status', Order::STATUS_FINISHED)
            // Author:Jerry Date:180827 添加订单查询筛选
            ->where('pay_target_type', Order::PAY_TARGET_TYPE_OPER)
            ->whereBetween('finish_time', [$this->start, $this->end])
            ->chunk(1000, function (Collection $orders) use ($merchant, $settlement){
                $orders->each(function($item) use ($merchant, $settlement){

                    // ChangeByJerry Date:180827 结算金额统计在订单内

                    $item->settlement_charge_amount = $item->pay_price * $item->settlement_rate / 100;  // 手续费
                    $item->settlement_real_amount = $item->pay_price - $item->settlement_charge_amount;   // 货款
                    $item->settlement_status = Order::SETTLEMENT_STATUS_FINISHED;
                    $item->settlement_id = $settlement->id;
                    $item->save();

                    // 结算实收金额
                    $settlement->amount += $item->pay_price;
                    $settlement->charge_amount += $item->settlement_real_amount;
                    $settlement->real_amount += $item->settlement_real_amount;
                });
            });

        $settlement->save();
    }
}
