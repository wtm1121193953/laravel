<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 16:10
 */

namespace App\Modules\User;


trait GenPassword
{

    /**
     * 用户密码加密
     * @param $password
     * @param $salt
     * @return string
     */
    public static function genPassword($password, $salt){
        return md5(md5($password) . $salt);
    }

}