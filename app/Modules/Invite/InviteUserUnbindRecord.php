<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\User\User;


class InviteUserUnbindRecord extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
