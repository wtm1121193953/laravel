<?php

use Illuminate\Database\Seeder;

class SettlementPlatformTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(\App\Modules\Settlement\SettlementPlatform::class, 5)->create();
    }
}
