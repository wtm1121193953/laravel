<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Order\OrderItem::class, function (Faker $faker) {
    return [
        //
        'status' => 1
    ];
});
