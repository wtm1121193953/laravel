<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Goods\Goods::class, function (Faker $faker) {
    $name = $faker->name;
    return [
        //
        'oper_id' => 1,
        'merchant_id' => 1,
        'name' => "$name 的商品",
        'desc' => "$name 的商品描述",
        'market_price' => rand(1, 500),
        'price' => rand(1, 500),
        'thumb_url' => $faker->imageUrl(190, 190),
        'pic' => $faker->imageUrl(750, 526),
        'pic_list' => implode(',', array_fill(0, rand(5, 10), $faker->imageUrl(750, 526))),
        'status' => 1
    ];
});
