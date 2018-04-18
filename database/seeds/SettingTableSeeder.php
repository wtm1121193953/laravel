<?php

use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \App\Modules\Setting\Setting::create([
            'key' => 'merchant_share_in_miniprogram',
            'value' => 1,
            'name' => '小程序端商户共享',
            'desc' => '不同运营中心的小程序共享用户间的商户与订单',
        ]);
    }
}
