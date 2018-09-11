<?php
/**
 * Created by PhpStorm.
 * User: evan.li
 * Date: 2018/6/7
 * Time: 21:44
 */

namespace App\Http\Controllers\UserApp;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Modules\User\UserService;
use App\Result;

class UserController extends Controller
{

    /**
     * 获取当前用户信息
     */
    public function getInfo()
    {
        $user = UserService::getinfoForUserApp();

        return Result::success([
            'userInfo' => $user
        ]);
    }
}