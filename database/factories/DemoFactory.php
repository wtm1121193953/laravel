<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Demo\Demo::class, function (Faker $faker) {
    return [
        //
        'name' => $faker->name,
        'status' => $faker->numberBetween(1, 2),
    ];
});
