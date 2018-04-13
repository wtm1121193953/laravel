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
        'origin_price' => rand(1, 500),
        'price' => rand(1, 500),
        'pic' => $faker->imageUrl(375, 200),
        'pic_list' => implode(',', array_fill(0, rand(5, 10), $faker->imageUrl(375, 200))),
        'status' => 1
    ];
});
