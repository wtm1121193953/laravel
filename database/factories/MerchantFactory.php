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
        'lng' => $faker->randomFloat(8, 110, 115),
        'lat' => $faker->randomFloat(8, 22, 26),
        'address' => $faker->address,
        'province' => $province->name,
        'province_id' => $province->area_id,
        'city' => '深圳市',
        'city_id' => 440300,
        'area' => $area->name,
        'area_id' => $area->area_id,
        'logo' => $faker->imageUrl(190, 190),
        'desc_pic' => $faker->imageUrl(750, 526),
        'business_licence_pic_url' => $faker->imageUrl(),
        'tax_cert_pic_url' => $faker->imageUrl(),
        'legal_id_card_pic_a' => $faker->imageUrl(),
        'legal_id_card_pic_b' => $faker->imageUrl(),
        'contract_pic_url' => $faker->imageUrl(),
        'licence_pic_url' => $faker->imageUrl(),
        'hygienic_licence_pic_url' => $faker->imageUrl(),
        'agreement_pic_url' => $faker->imageUrl(),
    ];
});
