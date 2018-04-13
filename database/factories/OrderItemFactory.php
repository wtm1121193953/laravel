<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Order\OrderItem::class, function (Faker $faker) {
    $verifyCode = $faker->unique()->randomNumber();
    return [
        //
        'oper_id' => 1,
        'merchant_id' => 1,
        'verify_code' => $verifyCode,
        'status' => 1
    ];
});
