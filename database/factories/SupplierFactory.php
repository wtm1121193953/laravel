<?php

use App\Modules\Supplier\Supplier;
use Faker\Generator as Faker;

$factory->define(Supplier::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->numberBetween(1, 2),
    ];
});
