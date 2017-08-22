<?php

use  Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * User: Evan Lee
 * Date: 2017/8/18
 * Time: 11:04
 */
class BossUserTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        $salt = 'salt';
        DB::table('boss_users')->insert([
            'id' => 1,
            'username' => 'admin',
            'password' => md5(md5('123456') . $salt),
            'salt' => $salt,
            'is_super' => 1,
        ]);
    }
}