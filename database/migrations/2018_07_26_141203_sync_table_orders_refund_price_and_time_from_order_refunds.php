<?php

use App\Modules\Order\Order;
use App\Modules\Order\OrderRefund;
use Illuminate\Database\Migrations\Migration;

class SyncTableOrdersRefundPriceAndTimeFromOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        OrderRefund::where('status', 2)
            ->chunk(1000, function($list){
                $list->each(function($item) {
                    Order::where('id', $item->order_id)
                        ->where('status', 6)
                        ->update([
                            'refund_price' => $item->amount,
                            'refund_time' => $item->created_at,
                        ]);
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
