<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\User\User;

class InviteUserRecord extends BaseModel
{
    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
