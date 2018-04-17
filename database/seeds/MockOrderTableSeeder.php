<?php

use Illuminate\Database\Seeder;

class MockOrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 创建order
        factory(\App\Modules\Order\Order::class, 100)->create();

        // 创建order item
        \App\Modules\Order\Order::chunk(100, function($orders){
            foreach ($orders as $order) {
                if($order->status === 4){
                    $orderItem = \App\Modules\Order\OrderItem::where('order_id', $order->id)->first();
                    if(empty($orderItem)){
                        factory(\App\Modules\Order\OrderItem::class, $order->buy_number)->create([
                            'order_id' => $order->id,
                            'oper_id' => $order->oper_id,
                            'merchant_id' => $order->merchant_id,
                            'verify_code' => \App\Modules\Order\OrderItem::createVerifyCode($order->merchant_id),
                            'created_at' => $order->pay_time,
                        ]);
                    }
                }
            }
        });
    }
}
