<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Merchant\MerchantCategory::class, function (Faker $faker) {
    return [
        //
        'pid' => rand(1, 20),
        'name' => $faker->name,
        'icon' => $faker->imageUrl(80, 80),
        'status' => 1
    ];
});
