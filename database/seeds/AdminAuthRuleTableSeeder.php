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
            ['id' => 2, 'name' => '用户管理', 'level' => 2, 'url' => '/admin/users', 'url_all' => '/admin/users,/api/admin/users,/api/admin/user/add,/api/admin/user/edit,/api/admin/user/changeStatus,/api/admin/user/resetPassword,/api/admin/user/del', 'sort' => 1, 'status' => 1, 'pid' => 1],
            ['id' => 3, 'name' => '角色管理', 'level' => 2, 'url' => '/admin/groups', 'url_all' => '/admin/groups,/api/admin/groups,/api/admin/group/add,/api/admin/group/edit,/api/admin/group/changeStatus,/api/admin/group/del,/api/admin/rules/tree', 'sort' => 2, 'status' => 1, 'pid' => 1],
            ['id' => 4, 'name' => '权限管理', 'level' => 2, 'url' => '/admin/rules', 'url_all' => '/admin/rules,/api/admin/rules,/api/admin/rule/add,/api/admin/rule/edit,/api/admin/rule/del,/api/admin/rule/changeStatus', 'sort' => 3, 'status' => 1, 'pid' => 1],
            ['id' => 5, 'name' => '运营中心管理', 'level' => 1, 'url' => '/admin/opers', 'url_all' => '/admin/opers,/api/admin/opers,/api/admin/oper/detail,/api/admin/oper/add,/api/admin/oper/edit,/api/admin/oper/changeStatus,/api/admin/oper_account/add,/api/admin/oper_account/edit,/api/admin/miniprogram/add,/api/admin/miniprogram/edit,/api/admin/miniprogram/uploadCert,/api/admin/miniprogram/uploadVerifyFile', 'sort' => 2, 'status' => 1, 'pid' => 0],
            ['id' => 6, 'name' => '商户管理', 'level' => 1, 'url' => 'merchant', 'url_all' => '', 'sort' => 3, 'status' => 1, 'pid' => 0],
            ['id' => 7, 'name' => '系统设置', 'level' => 1, 'url' => '/admin/setting', 'url_all' => '/admin/setting,/api/admin/settings,/api/admin/setting/edit', 'sort' => 4, 'status' => 1, 'pid' => 0],
            ['id' => 8, 'name' => '商户审核', 'level' => 2, 'url' => '/admin/merchants', 'url_all' => '/admin/merchants,/api/admin/merchants,/api/admin/merchant/detail,/api/admin/merchant/audit', 'sort' => 1, 'status' => 1, 'pid' => 6],
            ['id' => 9, 'name' => '商户池', 'level' => 2, 'url' => '/admin/merchant/pool', 'url_all' => '/admin/merchant/pool,/api/admin/merchant/pool,/api/admin/merchant/pool/detail', 'sort' => 3, 'status' => 1, 'pid' => 6],
        ]);
    }
}