<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:20
 */

namespace App\Modules\User;


use App\BaseService;

class UserService extends BaseService
{

    /**
     * 通过电话号码查询用户详情
     * @param $mobile
     * @return User
     */
    public static function getUserByMobile($mobile)
    {
        $user = User::where('mobile', $mobile)->first();
        return $user;
    }
}