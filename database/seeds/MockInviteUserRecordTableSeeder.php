<?php

use Illuminate\Database\Seeder;

class MockInviteUserRecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(\App\Modules\Invite\InviteUserRecord::class, 1000)->create();
    }
}
