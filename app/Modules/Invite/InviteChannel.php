<?php

namespace App\Modules\Invite;

use Illuminate\Database\Eloquent\Model;

class InviteChannel extends Model
{
    //推广人类型  1-用户
    const ORIGIN_TYPE_USER = 1;
    //推广人类型  2-商户
    const ORIGIN_TYPE_MERCHANT = 2;
    //推广人类型  3-运营中心
    const ORIGIN_TYPE_OPER = 3;
}
