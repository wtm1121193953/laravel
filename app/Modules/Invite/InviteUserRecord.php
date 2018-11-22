<?php

namespace App\Modules\Invite;

use App\BaseModel;
use App\Modules\User\User;

/**
 * Class InviteUserRecord
 * @package App\Modules\Invite
 *
 * @property number user_id`
 * @property number invite_channel_id
 * @property number origin_id
 * @property number origin_type
 */
class InviteUserRecord extends BaseModel
{
    //推广人类型  1-用户
    const ORIGIN_TYPE_USER = 1;
    //推广人类型  2-商户
    const ORIGIN_TYPE_MERCHANT = 2;
    //推广人类型  3-运营中心
    const ORIGIN_TYPE_OPER = 3;
    //推广人类型  5-超市
    const ORIGIN_TYPE_CS = 5;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $dispatchesEvents = [
        'created' => \App\Events\InviteUserRecordCreatedEvent::class,
    ];

    /*protected $dispatchesEvents = [
        'created' => \App\Events\InviteUserRecordsCreatedEvent::class,
    ];*/

}
