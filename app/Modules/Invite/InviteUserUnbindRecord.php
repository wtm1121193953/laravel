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
 * @property string mobile
 * @property integer batch_record_id
 * @property string old_invite_user_record
 */
class InviteUserUnbindRecord extends BaseModel
{
    //状态 1-未解绑 2-已解绑
    const STATUS_BIND = 1;
    const STATUS_UNBIND = 2;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
