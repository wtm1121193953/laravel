<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(SettingTableSeeder::class);
        $this->call(AdminAuthRuleTableSeeder::class);
        $this->call(AdminUserTableSeeder::class);
        $this->call(AreaTableSeeder::class);
        $this->call(MerchantCategoryTableSeeder::class);

    }
}
