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
        $this->call(AdminAuthRuleTableSeeder::class);
        $this->call(AdminUserTableSeeder::class);

        // 模拟数据填充
        if(App::environment('local')){
            $this->call(SupplierTableSeeder::class);
            $this->call(CategoryTableSeeder::class);

            $this->call(DemoTableSeeder::class);
        }
    }
}
