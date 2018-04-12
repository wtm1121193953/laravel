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
            'name' => 'mock miniprogram name ',
            'appid' => 'mock appid',
            'secret' => 'mock secret',
            'mch_id' => 'mock mch_id',
        ]);

        // 填充商家分类数据
        factory(\App\Modules\Merchant\MerchantCategory::class, 20)->create([
            'pid' => 0
        ]);
        factory(\App\Modules\Merchant\MerchantCategory::class, 100)->create();

        // 填充商家数据
        factory(\App\Modules\Merchant\Merchant::class, 100)->create();

        // 填充商品数据
        factory(\App\Modules\Goods\Goods::class, 50)->create();
    }
}
