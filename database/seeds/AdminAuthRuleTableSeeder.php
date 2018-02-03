<?php

use  Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * User: Evan Lee
 * Date: 2017/8/18
 * Time: 11:43
 */
class AdminAuthRuleTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        DB::table('admin_auth_rules')->insert([
            ['id' => 1, 'name' => '权限', 'level' => 1, 'url' => '', 'url_all' => '', 'sort' => 1, 'status' => 1, 'pid' => 0],
            ['id' => 2, 'name' => '用户管理', 'level' => 2, 'url' => '/admin/users',
             'url_all' => '/admin/users,/api/admin/users', 'sort' => 1, 'status' => 1, 'pid' => 1],
            ['id' => 3, 'name' => '角色管理', 'level' => 2, 'url' => '/admin/groups',
             'url_all' => '/admin/groups,/api/admin/groups', 'sort' => 2, 'status' => 1, 'pid' => 1],
            ['id' => 4, 'name' => '权限管理', 'level' => 2, 'url' => '/admin/rules',
             'url_all' => '/admin/rules,/api/admin/rules,/api/admin/rules/top', 'sort' => 3, 'status' => 1, 'pid' => 1],
        ]);
    }
}