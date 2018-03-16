<?php

use Illuminate\Database\Seeder;

class DemoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(\App\Modules\Demo\Demo::class, 156)->create();
    }
}
