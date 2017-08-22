<?php

namespace App\Modules\BossAuth;

use App\Modules\BaseModel;

class BossUser extends BaseModel
{
    // 序列化是隐藏密码
    protected $hidden = ['password', 'salt'];
}
