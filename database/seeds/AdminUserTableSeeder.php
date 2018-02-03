<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $salt = 'salt';
        DB::table('admin_users')->insert([
            'id' => 1,
            'username' => 'admin',
            'password' => \App\Modules\Admin\AdminUser::genPassword('123456', $salt),
            'salt' => $salt,
            'super' => 1,
        ]);
    }
}
