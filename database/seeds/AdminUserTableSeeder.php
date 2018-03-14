<?php

use App\Modules\Admin\AdminUser;
use Illuminate\Database\Seeder;

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
        AdminUser::create([
            'username' => 'admin',
            'password' => AdminUser::genPassword('123456', $salt),
            'salt' => $salt,
            'super' => 1,
        ]);
    }
}
