<?php

use App\Modules\Settlement\SettlementPlatform;
use Faker\Generator as Faker;

$factory->define(SettlementPlatform::class, function (Faker $faker) {
    $rate = $faker->numberBetween(10, 20);
    $amount = $faker->randomFloat(2, 10, 100000);
    return [
        //
        'oper_id' => 3,
        'merchant_id' => 3,
        'start_date' => $faker->date(),
        'end_date' => $faker->date(),
        'settlement_rate' => $rate,
        'amount' => $amount,
        'charge_amount' => $amount * $rate / 100,
        'real_amount' => $amount * (1 -  $rate / 100),
        'bank_open_name' => '',
        'bank_card_no' => $faker->numberBetween(100000000),
        'sub_bank_name' => $faker->iban(),
        'bank_open_address' => $faker->address,
        'pay_pic_url' => $faker->image(),
        'invoice_title' => '',
        'invoice_no' => '',
        'invoice_type' => $faker->numberBetween(1, 2),
        'invoice_pic_url' => $faker->image(),
        'logistics_name' => '',
        'status' => $faker->numberBetween(1, 4),
        'created_at' => $faker->dateTime,
        'logistics_no' => ''

    ];
});
