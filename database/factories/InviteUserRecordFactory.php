<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Invite\InviteUserRecord::class, function (Faker $faker) {
    $userIds = [1, 2, 3, 4, 5];
    return [
        //
        'user_id' => $faker->unique()->numberBetween(0, 10000),
        'origin_id' => $faker->randomElement($userIds),
        'origin_type' => $faker->randomElement([1, 2, 3])
    ];
});
