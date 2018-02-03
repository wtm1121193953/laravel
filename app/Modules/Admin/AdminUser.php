<?php

namespace App\Modules\Admin;

use App\BaseModel;

class AdminUser extends BaseModel
{
    //

    /**
     * 用户密码加密
     * @param $password
     * @param $salt
     * @return string
     */
    public static function genPassword($password, $salt){
        return md5(md5($password) . $salt);
    }

    public function isSuper()
    {
        return $this->super == 1;
    }

}
