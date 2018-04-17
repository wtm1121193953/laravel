<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Order\Order::class, function (Faker $faker) {
    $number = rand(1, 13);

//    $opers = \App\Modules\Oper\Oper::all();
    $oper = \App\Modules\Oper\Oper::find(1);
    $merchants = \App\Modules\Merchant\Merchant::where('oper_id', $oper->id)->get();
    $merchant = $merchants->random();
    $goodsList = \App\Modules\Goods\Goods::where('merchant_id', $merchant->id)->get();
    $goods = $goodsList->random();
    $userOpenIdMappings = \App\Modules\User\UserOpenIdMapping::where('oper_id', $oper->id)->get();
    $userOpenIdMapping = $userOpenIdMappings->random();
    $user = \App\Modules\User\User::find($userOpenIdMapping->user_id);
    $createTime = $faker->dateTimeBetween('-7 days');
    return [
        //
        'oper_id' => $oper->id,
        'order_no' => 'O' . $createTime->format('YmdHis') . rand(100000, 999999),
        'user_id' => $user->id,
        'open_id' => $userOpenIdMapping->open_id,
        'user_name' => $user->name || '',
        'notify_mobile' => $user->mobile,
        'merchant_id' => $merchant->id,
        'merchant_name' => $merchant->name,
        'goods_id' => $goods->id,
        'goods_name' => $goods->name,
        'goods_pic' => $goods->pic,
        'goods_thumb_url' => $goods->thumb_url,
        'price' => $goods->price,
        'buy_number' => $number,
        'status' => 4,
        'pay_price' => $goods->price * $number,
        'pay_time' => (new \Illuminate\Support\Carbon($createTime->format('Y-m-d H:i:s')))->addMinutes(6),
        'created_at' => $createTime,
    ];
});
