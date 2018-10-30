<?php

namespace App\Modules\User;

use App\BaseModel;

/**
 * Class UserStatistics
 * @package App\Modules\User
 *
 * @property string date
 * @property integer user_id
 * @property integer invite_user_num
 * @property float order_finished_amount
 * @property integer order_finished_num
 */

class UserStatistics extends BaseModel
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
