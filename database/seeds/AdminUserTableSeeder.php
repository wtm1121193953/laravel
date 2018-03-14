<?php

use App\Modules\Admin\AdminUser;
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
        $user = AdminUser::create([
            'username' => 'admin',
            'password' => AdminUser::genPassword('123456', $salt),
            'salt' => $salt,
        ]);
        $user->assignRole('super-admin');
    }
}
