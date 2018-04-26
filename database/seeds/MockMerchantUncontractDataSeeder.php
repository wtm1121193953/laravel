<?php

use Illuminate\Database\Seeder;

class MockMerchantUncontractDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 填充商家数据
        factory(\App\Modules\Merchant\Merchant::class, 100)->create([
            'contract_status' => 2,
            'creator_oper_id' => 1,
            'oper_id' => 0,
        ]);
    }
}
