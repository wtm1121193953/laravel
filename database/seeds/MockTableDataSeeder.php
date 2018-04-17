<?php

use App\Modules\Oper\Oper;
use Illuminate\Database\Seeder;

/**
 * 模拟的测试数据
 * Class MockTableDataSeeder
 */
class MockTableDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 模拟运营中心数据
        Oper::create([
            'id' => 1,
            'name' => 'mock oper',
        ]);
        \App\Modules\Oper\OperMiniprogram::create([
            'oper_id' => 1,
            'name' => '大千生活',
            'appid' => 'wx1abb4cf60ffea6c9',
            'secret' => 'f8f1ee6243170330a21105d7c3712143',
            'mch_id' => 'mock mch_id',
        ]);
        // 模拟运营中心用户
        $salt = str_random();
        \App\Modules\Oper\OperAccount::create([
            'oper_id' =>1,
            'name' => 'oper account name',
            'account' => 'oper',
            'salt' => $salt,
            'password' => \App\Modules\Oper\OperAccount::genPassword('123456', $salt),
        ]);

        // 填充商家数据
        factory(\App\Modules\Merchant\Merchant::class, 100)->create();
        // 模拟商家账户
        $salt = str_random();
        \App\Modules\Merchant\MerchantAccount::create([
            'oper_id' => 1,
            'merchant_id' => 1,
            'name' => 'merchant account name',
            'account' => 'merchant',
            'salt' => $salt,
            'password' => \App\Modules\Merchant\MerchantAccount::genPassword('123456', $salt),
        ]);

        // 填充商品数据
        factory(\App\Modules\Goods\Goods::class, 50)->create();

        // 模拟用户数据
        \App\Modules\User\User::create([
            'id' => 1,
            'name' => 'mock user',
            'mobile' => '13800138000',
        ]);
        \App\Modules\User\UserOpenIdMapping::create([
            'oper_id' => 1,
            'user_id' => 1,
            'open_id' => 'mock open id',
        ]);
    }
}
