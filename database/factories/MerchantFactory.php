<?php

use Faker\Generator as Faker;

$factory->define(\App\Modules\Merchant\Merchant::class, function (Faker $faker) {
    $allAreas = \App\Modules\Area\Area::all();
    $province = $allAreas->filter(function($item){ return $item->path == 1;})->random();
    $city = $allAreas->filter(function($item){ return $item->path == 2;})->random();
    $area = $allAreas->filter(function($item){ return $item->path == 3;})->random();
    return [
        //
        'oper_id' => 1,
        'merchant_category_id' => rand(1, 100),
        'name' => $faker->name . '的商家',
        'status' => 1,
        'lng' => $faker->randomFloat(8, 100, 200),
        'lat' => $faker->randomFloat(8, 10, 80),
        'address' => $faker->address,
        'province' => $province->name,
        'province_id' => $province->area_id,
        'city' => $city->name,
        'city_id' => $city->area_id,
        'area' => $area->name,
        'area_id' => $area->area_id,
    ];
});
