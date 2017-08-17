<?php
/**
 * User: Evan Lee
 * Date: 2017/8/16
 * Time: 18:17
 */

namespace App\Http\Controllers\Boss;


use App\Modules\BossAuth\BossAuthGroup;
use App\Modules\BossAuth\BossAuthService;
use App\Modules\BossAuth\BossUserService;

class LoginController
{

    public function test()
    {
        dump(BossAuthService::$a);
        BossAuthService::$a = 'bbbbb';
        dump(BossAuthService::$a);
        dump(BossUserService::$a);
    }
}