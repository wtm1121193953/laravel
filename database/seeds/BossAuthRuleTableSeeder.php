<?php

use  Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * User: Evan Lee
 * Date: 2017/8/18
 * Time: 11:43
 */
class BossAuthRuleTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        DB::table('boss_auth_rules')->insert([
            ['id' => 1, 'name' => '权限', 'level' => 1, 'url' => '', 'url_all' => '', 'sort' => 1, 'status' => 1, 'pid' => 0],
            ['id' => 2, 'name' => '用户管理', 'level' => 2, 'url' => '/boss/user/list',
             'url_all' => '/boss/user/list,/api/boss/users', 'sort' => 1, 'status' => 1, 'pid' => 1],
            ['id' => 3, 'name' => '角色管理', 'level' => 2, 'url' => '/boss/group/list',
             'url_all' => '/boss/group/list,/api/boss/groups', 'sort' => 2, 'status' => 1, 'pid' => 1],
            ['id' => 4, 'name' => '权限管理', 'level' => 2, 'url' => '/boss/rule/list',
             'url_all' => '/boss/rule/list,/api/boss/rules,/api/boss/rule/getTopList', 'sort' => 3, 'status' => 1, 'pid' => 1],
        ]);
    }
}