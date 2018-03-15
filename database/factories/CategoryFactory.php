<?php

use App\Modules\Item\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->numberBetween(1, 2),
    ];
});
