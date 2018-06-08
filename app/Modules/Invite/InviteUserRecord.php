<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\User\User;

class InviteUserRecord extends InviteChannel
{
    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
