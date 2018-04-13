<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Order\Order::class, function (Faker $faker) {
    $number = rand(1, 13);
    $id = $faker->unique()->randomNumber();
    factory(\App\Modules\Order\OrderItem::class, $number)->create([
        'order_id' => $id,
    ]);
    $goods = \App\Modules\Goods\Goods::find(1);
    return [
        //
        'id' => $id,
        'oper_id' => 1,
        'order_no' => 'O' . $faker->date('YmdHis') . rand(100000, 999999),
        'user_id' => 1,
        'user_name' => 'mock user',
        'merchant_id' => 1,
        'merchant_name' => 'mock merchant',
        'goods_id' => $goods->id,
        'goods_name' => $goods->name,
        'goods_pic' => $goods->pic,
        'price' => $goods->price,
        'buy_number' => $number,
        'status' => 1,
        'pay_price' => $goods->price * $number,
        'pay_time' => $faker->dateTime(),
    ];
});
