<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\User\User;

/**
 * Class InviteUserUnbindRecord
 * @package App\Modules\Invite
 *
 * @property number user_id`
 * @property number status
 */
class InviteUserUnbindRecord extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
